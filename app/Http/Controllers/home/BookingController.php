<?php

namespace App\Http\Controllers\home;

use App\Models\Food;
use App\Models\Facility;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Rating;
use App\Models\Booking;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $venues = Facility::with('picture', 'rating')
            ->whereNull('deleted_at');

        $isFiltering = false; // <–– default

        $startDate = $request->input('checkin');
        $endDate = $request->input('checkout');

        if ($startDate && $endDate) {

            $isFiltering = true; // <–– filtering triggered

            $startDateFormatted = date('Y-m-d', strtotime($startDate));
            $endDateFormatted = date('Y-m-d', strtotime($endDate));

            $venues = $venues->whereDoesntHave('bookings', function ($query) use ($startDateFormatted, $endDateFormatted) {
                $query->whereNotIn('status', ['Cancel', 'Full Paid'])
                      ->where(function ($q) use ($startDateFormatted, $endDateFormatted) {
                          $q->whereBetween('check_in', [$startDateFormatted, $endDateFormatted])
                            ->orWhereBetween('check_out', [$startDateFormatted, $endDateFormatted])
                            ->orWhere(function ($q2) use ($startDateFormatted, $endDateFormatted) {
                                $q2->where('check_in', '<=', $startDateFormatted)
                                  ->where('check_out', '>=', $endDateFormatted);
                            });
                      });
            });
        }

        $venues = $venues->get();

        return view('content.booking.booking', compact('venues', 'isFiltering'));
    }

    public function viewDetails($id){
      $decryptedId = Crypt::decryptString($id);
      $venue = Facility::with('picture', 'promo', 'rating')
        ->where('id', $decryptedId)
        ->whereNull('deleted_at')
        ->first();

      $ratings = Rating::with('images')
        ->leftjoin('bookings', 'bookings.id', '=', 'rating.bookings_id')
        ->leftjoin('facilities', 'facilities.id', '=', 'rating.facilities_id')
        ->leftjoin('users', 'users.id', '=', 'bookings.users_id')
        ->leftjoin('person', 'person.id', '=', 'users.person_id')
        ->where('facilities.id', $decryptedId)
        ->select('rating.*','rating.created_at as CommentDate','person.firstname','person.middlename','person.lastname','users.email')
        ->orderBy('bookings.created_at', 'Desc')
        ->get();
      
      $bookingDetails = Booking::with('facility')
        ->where('bookings.status', '!=', 'cancel')
        ->whereHas('facility', function ($query) use ($decryptedId) {
            $query->where('id', $decryptedId);
        })
        ->get()->map(function ($data) {
              return [
                  'start' => date('Y-m-d', strtotime($data->check_in)),
                  'end'   => date('Y-m-d', strtotime($data->check_out)), 
                  'time_in' => $data->check_in,
                  'time_out' => $data->check_in 
              ];
        });
      
      return view('content.booking.facility_details', compact('venue', 'ratings','bookingDetails'));
    }
    public function viewFoods(Request $request){

      if (empty($request->all())) {
        return redirect()->route('booking')->with('error', 'No food items selected.');
      }
      $bookingDetails = $request->all();

      $foods = Food::with('picture')
        ->whereNull('deleted_at')
        ->get();


      return view('content.booking.food_details', compact('bookingDetails', 'foods'));
    }
    public function displayPDF(Request $request) {
      $Id = $request->id;
      $bookingId = Crypt::decryptString($Id);
      
      $booking = Booking::leftjoin('facilities', 'bookings.facilities_id', '=', 'facilities.id')
        ->leftjoin('promos', 'bookings.promos_id', '=', 'promos.id')
        ->where('bookings.id', $bookingId)
        ->select('bookings.*', 'facilities.name as facility_name', 'facilities.price as facility_price', 'promos.*')
        ->first();

      $food = Food::leftjoin('food_bookings', 'foods.id', '=', 'food_bookings.foods_id')
        ->leftjoin('bookings', 'food_bookings.bookings_id', '=', 'bookings.id')
        ->where('bookings.id', $bookingId)
        ->get();
      // Dummy data for testing
      $bookingDetails = [
        'name' => $booking->name ?? $booking->facility_name,
        'price' => $booking->price ?? $booking->facility_price,
        'check_in' => $booking->check_in,
        'check_out' => $booking->check_out,
        'date' => now(),
        'promo' => $booking->name,
        'service_fee' => 0,
        'total_amount' => $booking->amount,
        'status' => $booking->status,
      ];
      $foodItems = [];
      foreach( $food as $item ){
        $foodItems[] = [
          'name' => $item->name,
          'price' => $item->price,
          'quantity' => $item->quantity,
        ];
      }
      $pdf = Pdf::loadView('pdf.bookingPDF', [
        'Details' => $bookingDetails,
        'Foods' => $foodItems,
      ]);
      $pdf->setpaper('A6', 'portrait');
      return $pdf->stream('reciept.pdf');
    }
}
