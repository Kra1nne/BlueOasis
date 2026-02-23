<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\Food;
use App\Models\FoodBooking;
use App\Models\Facility;
use App\Models\Rating;
use Carbon\Carbon;
use DB;


class Analytics extends Controller
{
  public function index(Request $request)
  {        
                           
      
      $selectedYear = $request->input('year', now()->year);

      $monthFood = [];
      for ($i = 0; $i < 12; $i++) {
          $m = Carbon::now()->subMonths($i);
          $months0[$m->format('Y-m')] = $m->format('F Y');
      }

      $monthFacility = [];
      for ($i = 0; $i < 12; $i++) {
          $m = Carbon::now()->subMonths($i);
          $months1[$m->format('Y-m')] = $m->format('F Y');
      }

      $foodCount = Food::whereNull('deleted_at')->count();
      $facilitycount = Facility::whereNull('deleted_at')->count();
      
      $ratingavg = Rating::whereNull('deleted_at')->avg('rating');
      $customerSatisfaction = $ratingavg ? round(($ratingavg / 5) * 100, 1) : 0;

      $ratingCounts = Rating::whereNull('deleted_at')
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->pluck('count', 'rating')
            ->toArray();
      
      // Ensure all rating values (1-5) exist in the array
      $ratingCounts = array_replace([5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0], $ratingCounts);

      $amountReservation = Payment::orderBy('created_at', 'desc')->get();

      $availableYears = Payment::selectRaw('YEAR(created_at) as year')
          ->distinct()
          ->pluck('year')
          ->toArray();

      $monthlyPayments = Payment::selectRaw('MONTH(created_at) as month, SUM(amount) as total')
          ->whereYear('created_at', $selectedYear)
          ->groupBy('month')
          ->orderBy('month')
          ->pluck('total', 'month')
          ->toArray();

      $monthlyPartialPayments = Payment::leftjoin('bookings', 'bookings.id', '=', 'payments.bookings_id')
            ->selectRaw("
                MONTH(payments.created_at) as month,
                SUM(
                    CASE 
                        WHEN payments.status = 'Fully Paid' AND bookings.walk_in != 0 THEN payments.amount / 2
                        WHEN payments.status = 'Cancel' OR payments.status = 'Partial Payment' THEN payments.amount
                        ELSE 0
                    END
                ) as total
            ")
            ->whereYear('payments.created_at', $selectedYear)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();


      $months = range(1, 12);
      $monthlyData = array_map(fn($month) => $monthlyPayments[$month] ?? 0, $months);
      $monthlyPartialData = array_map(fn($month) => $monthlyPartialPayments[$month] ?? 0, $months);

      $reservations = Booking::with('facility')
          ->leftJoin('users', 'bookings.users_id', '=', 'users.id')
          ->orderBy('bookings.updated_at', 'desc')
          ->limit(8)
          ->get();

      $payment = Payment::orderBy('created_at', 'desc')->limit(5)->get();

      $amount = Payment::sum('amount');

      $totalReservations = Booking::count();
      $averageRevenuePerReservation = $totalReservations > 0
            ? round($amount / $totalReservations, 2)
            : 0;

        // --- Revenue trend (for sparkline)
      $avgRevenueTrend = Payment::selectRaw('MONTH(created_at) as month, AVG(amount) as avg_amount')
            ->whereYear('created_at', $selectedYear)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('avg_amount', 'month')
            ->toArray();

       $avgRevenueTrend = array_values(array_replace(array_fill(1, 12, 0), $avgRevenueTrend));

       $refundsData = Booking::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(amount) as total')
        )
        ->whereYear('created_at', $selectedYear)
        ->where('status', 'Cancel')
        ->groupBy('month')
        ->orderBy('month')
        ->pluck('total', 'month')
        ->toArray();

        // Fill missing months with 0
        $refundsData = collect(range(1, 12))->map(fn($m) => $refundsData[$m] ?? 0);

        $availableYearsBooking = Booking::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->map(fn($y) => (int)$y); // ensure integers
        
        // If no bookings exist, default to current year
        $year = $request->input('year', $availableYearsBooking->first() ?? now()->year);

        $roomData = Booking::leftJoin('facilities', 'facilities.id', '=', 'bookings.facilities_id')
            ->where('facilities.category', 'room')
            ->whereYear('bookings.created_at', $year)
            ->sum('bookings.facility_income');

        $cottageData = Booking::leftJoin('facilities', 'facilities.id', '=', 'bookings.facilities_id')
            ->where('facilities.category', 'cottage')
            ->whereYear('bookings.created_at', $year)
            ->sum('bookings.facility_income');

        
        $totalRevenue = Booking::sum('facility_income');


        $totalReservations = Booking::count();

        $averageRevenue = $totalReservations > 0 ? $totalRevenue / $totalReservations : 0;
        $monthlyRevenue = [];

        foreach ($months as $month) {
            $monthlyRevenue[] = Booking::sum(DB::raw("CASE WHEN MONTH(created_at) = $month THEN facility_income ELSE 0 END"));
        }
        $monthFacility = $request->input('monthFacilities', now()->format('Y-m'));

        $facilitiesData = Facility::leftJoin('bookings', function($join) use ($monthFacility) {
                            $join->on('bookings.facilities_id', '=', 'facilities.id');
                            if ($monthFacility) {
                                $join->whereRaw("DATE_FORMAT(bookings.created_at, '%Y-%m') = ?", [$monthFacility]);
                            }
                        })
                        ->whereNull('facilities.deleted_at')
                        ->groupBy('facilities.id', 'facilities.name')
                        ->select(
                            'facilities.name',
                            DB::raw('COALESCE(count(bookings.id), 0) as total')
                        )
                        ->get();

        $monthFood = $request->input('month', now()->format('Y-m'));

        $foodData = Food::leftJoin('food_bookings', function($join) use ($monthFood) {
            $join->on('food_bookings.foods_id', '=', 'foods.id');
            if ($monthFood) {
                // Apply month filter inside the LEFT JOIN to keep all foods
                $join->whereRaw("DATE_FORMAT(food_bookings.created_at, '%Y-%m') = ?", [$monthFood]);
            }
        })
        ->whereNull('foods.deleted_at')
        ->groupBy('foods.id', 'foods.name')
        ->select(
            'foods.name',
            DB::raw('COALESCE(SUM(foods.price * food_bookings.quantity), 0) as total')
        )
        ->get();

        $RoomBest = Facility::leftJoin('bookings', 'bookings.facilities_id', '=', 'facilities.id')
                   ->leftjoin('pictures', 'facilities.id', '=', 'pictures.facilities_id')
                   ->whereNull('facilities.deleted_at')
                   ->where('facilities.category', '=', 'room')
                   ->groupBy('facilities.id', 'facilities.name', 'pictures.path')  // add pictures.path to groupBy
                   ->select(
                       'facilities.name',
                       'pictures.path',
                       DB::raw('COALESCE(SUM(bookings.amount), 0) as total')
                   )
                   ->orderBy('total', 'desc')
                   ->first();  // Fetch the actual record here
        
        $CottageBest = Facility::leftJoin('bookings', 'bookings.facilities_id', '=', 'facilities.id')
                                ->leftjoin('pictures', 'facilities.id', '=', 'pictures.facilities_id')
                                ->whereNull('facilities.deleted_at')
                                ->where('facilities.category', '=', 'cottage')
                                ->groupBy('facilities.id', 'facilities.name', 'pictures.path')  
                                ->select(
                                    'facilities.name',
                                    'pictures.path',
                                    DB::raw('COALESCE(SUM(bookings.amount), 0) as total')  // COALESCE converts null to 0
                                )
                                ->orderBy('total', 'desc')
                                ->first();

        $foodBest = Food::leftjoin('food_bookings', 'food_bookings.foods_id', '=', 'foods.id')
                        ->leftjoin('pictures', 'pictures.foods_id', '=', 'foods.id')
                        ->whereNull('foods.deleted_at')
                        ->groupBy('foods.id', 'foods.name', 'pictures.path')
                        ->select(
                            'foods.name',
                            'pictures.path',
                            DB::raw('COALESCE(SUM(foods.price * food_bookings.quantity), 0) as total')  // COALESCE converts null to 0
                        )
                        ->orderBy('total', 'desc')
                        ->first();

        $CountOfBookings = Booking::leftjoin('facilities', 'bookings.facilities_id', '=', 'facilities.id')
                            ->groupBy('facilities.id', 'facilities.name')
                            ->select(
                                'facilities.name',
                                DB::raw('COUNT(bookings.id) as total_bookings'),
                                DB::raw('SUM(bookings.amount) as total_sales')
                            )
                            ->whereNull('facilities.deleted_at')
                            ->orderBy('total_sales', 'desc')
                            ->get();
        $CountOfFoodBookings = Food::leftjoin('food_bookings', 'food_bookings.foods_id', '=', 'foods.id')
                            ->groupBy('foods.id', 'foods.name')
                            ->select(
                                'foods.name',
                                DB::raw('COUNT(food_bookings.id) as total_orders'),
                                DB::raw('SUM(foods.price * food_bookings.quantity) as total_sales')
                            )
                            ->whereNull('foods.deleted_at')
                            ->orderBy('total_sales', 'desc')
                            ->get();                    
                            
      return view('content.dashboard.dashboards-analytics', compact(
          'reservations',
          'payment',
          'amount',
          'amountReservation',
          'monthlyData',
          'selectedYear',
          'availableYears',
          'foodCount',
          'facilitycount',
          'ratingavg',
          'customerSatisfaction',
          'refundsData',
          'roomData',
          'cottageData',
          'monthlyRevenue',
          'averageRevenue',
          'monthlyPartialData',
          'facilitiesData',
          'foodData',
          'foodBest',
          'CottageBest',
          'RoomBest',
          'monthFood',
          'monthFacility',
          'availableYearsBooking',
          'year',
          'ratingCounts',
          'CountOfBookings',
          'CountOfFoodBookings',
      ));
  }

}
