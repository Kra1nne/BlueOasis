<?php

namespace App\Http\Controllers\reservation;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\Food;
use App\Models\FoodBooking;
use App\Models\Log;
use App\Models\Payment;
use App\Models\Pool;
use App\Models\Rating;
use App\Models\RatingImages;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class ReservationController extends Controller
{
    public function index(){
      $facilities = Facility::leftjoin('promos','facilities.id', '=', 'promos.facilities_id')
                      ->whereNull('facilities.deleted_at')
                       ->select(
                            'facilities.id as facility_id',
                            'facilities.name as facility_name',
                            'promos.id as promo_id',
                            'promos.name as promo_name'
                        )
                      ->get();
      
      $reservations = Booking::with('foods')
                    ->leftjoin('facilities', 'facilities.id', '=', 'bookings.facilities_id')
                    ->leftjoin('users', 'users.id', '=', 'bookings.users_id')
                    ->leftjoin('payments', 'payments.bookings_id', '=', 'bookings.id')
                    ->leftjoin('person', 'person.id', '=', 'users.person_id')
                    ->leftjoin('rating', 'rating.bookings_id', '=', 'bookings.id')
                    ->leftjoin('pool', 'pool.bookings_id', '=', 'bookings.id')
                    ->leftJoin('promos', function($join) {
                        $join->on('promos.facilities_id', '=', 'facilities.id')
                            ->whereColumn('promos.id', 'bookings.promos_id');
                    })
                    ->orderByRaw("
                          CASE 
                              WHEN NOW() BETWEEN bookings.check_in AND bookings.check_out THEN 1
                              WHEN bookings.check_in > NOW() THEN 2
                              ELSE 3
                          END ASC
                      ")
                    ->orderBy('bookings.created_at', 'DESC')
                    ->distinct('bookings.id')
                    ->select('bookings.*', 'facilities.name as facilities_name', 'promos.name as promos_name', 'promos.price as promos_price', 'facilities.price as facilities_price','person.firstname', 'person.middlename', 'person.lastname', 'rating.rating as rate', 'payments.amount as payment_amount', 'payments.id as payment_id', 'facilities.category as category', 'payments.status as payment_status', 'facilities.additional_price_time', 'pool.adult_count', 'pool.children_count', 'pool.total_amount')
                    ->get()->map(function ($reservation) {
                          $reservation->encrypted_id = Crypt::encryptString($reservation->id);
                          return $reservation;
                      });
      
      return view('content.reservation.reservation-list', compact('reservations', 'facilities'));
    }

    public function userReservationDisplay(){
       $reservations = Booking::with('foods')
                    ->leftjoin('facilities', 'facilities.id', '=', 'bookings.facilities_id')
                    ->leftjoin('users', 'users.id', '=', 'bookings.users_id')
                    ->leftjoin('payments', 'payments.bookings_id', '=', 'bookings.id')
                    ->leftjoin('person', 'person.id', '=', 'users.person_id')
                    ->leftjoin('rating', 'rating.bookings_id', '=', 'bookings.id')
                    ->leftjoin('pool', 'pool.bookings_id', '=', 'bookings.id')
                    ->leftJoin('promos', function($join) {
                        $join->on('promos.facilities_id', '=', 'facilities.id')
                            ->whereColumn('promos.id', 'bookings.promos_id');
                    })
                    ->orderByRaw("
                          CASE 
                              WHEN NOW() BETWEEN bookings.check_in AND bookings.check_out THEN 1
                              WHEN bookings.check_in > NOW() THEN 2
                              ELSE 3
                          END ASC
                      ")
                    ->orderBy('bookings.created_at', 'DESC')
                    ->where('users.id', '=', Auth::id())
                    ->distinct('bookings.id')
                    ->select('bookings.*', 'facilities.name as facilities_name', 'promos.name as promos_name', 'promos.price as promos_price', 'facilities.price as facilities_price','person.firstname', 'person.middlename', 'person.lastname', 'rating.rating as rate', 'payments.amount as payment_amount', 'payments.id as payment_id', 'facilities.category as fac_category', 'pool.adult_count', 'pool.children_count', 'pool.total_amount')
                    ->get()->map(function ($reservation) {
                          $reservation->encrypted_id = Crypt::encryptString($reservation->id);
                          return $reservation;
                      });
      
      return view('content.reservation.user_reservation-list', compact('reservations'));
    }
    public function rating(Request $request){      
      $data = [
        'rating' => $request->rating,
        'comments' => $request->description,
        'bookings_id' => $request->bookings_id,
        'facilities_id' => $request->facilities_id,
        'created_at' => now()
      ];
      $rating = Rating::create($data);

      if ($request->hasFile('imagesData')) {
        foreach ($request->file('imagesData') as $image) {
            $path = $image->store('public/uploads');
              RatingImages::insert([
            'path' => Storage::url($path),
            'rating_id' => $rating->id,
            'uploaded_at' => now(),
        ]);
          }
      }

      $logData = [
        'user_id' => Auth::id(),
        'action' => 'Add',
        'table_name' => 'Rating',
        'description' => 'Added a Comments',
        'ip_address' => request()->ip(),
        'created_at' => now(),
      ];

      $resultLogs = Log::insert($logData);
      
      if($rating){
        return response()->json(['Error' => 0, 'Message' => 'Successfully added a Rating']);
      }
    }

    public function done(Request $request)
    {
      $bookingsData = [
        'status' => "Fully Paid",
        'updated_at' => now()
      ];
      Booking::where('id', $request->id)->update($bookingsData);
       $logData = [
        'user_id' => Auth::id(),
        'action' => 'Update',
        'table_name' => 'Bookings',
        'description' => 'Update a Status',
        'ip_address' => request()->ip(),
        'created_at' => now(),
      ];

      Log::insert($logData);

      $paymentData = [
        'status' => "Fully Paid",
        'updated_at' => now(),
        'amount' => ($request->amount + $request->amount)
      ];

      Payment::where('id', $request->payment_id)->update($paymentData);
      $logData = [
        'user_id' => Auth::id(),
        'action' => 'Update',
        'table_name' => 'Payment',
        'description' => 'Update a Payment',
        'ip_address' => request()->ip(),
        'created_at' => now(),
      ];

      $resultLogs = Log::insert($logData);

      if($resultLogs){
        return response()->json(['Error' => 0, 'Message' => 'Reservation payment successfully done.']);
      }
    }
    public function cancel(Request $request)
    {
      $bookingsData = [
        'status' => "Cancel",
        'updated_at' => now()
      ];
      Booking::where('id', $request->id)->update($bookingsData);
       $logData = [
        'user_id' => Auth::id(),
        'action' => 'Update',
        'table_name' => 'Bookings',
        'description' => 'Update a Status',
        'ip_address' => request()->ip(),
        'created_at' => now(),
      ];

      Log::insert($logData);

      $paymentData = [
        'status' => "Cancel",
        'updated_at' => now(),
      ];

      Payment::where('id', $request->payment_id)->update($paymentData);
      $logData = [
        'user_id' => Auth::id(),
        'action' => 'Update',
        'table_name' => 'Payment',
        'description' => 'Update a Payment',
        'ip_address' => request()->ip(),
        'created_at' => now(),
      ];

      $resultLogs = Log::insert($logData);

      if($resultLogs){
        return response()->json(['Error' => 0, 'Message' => 'Successfully cancel your reservation.']);
      }
    }
    public function updateReservation(Request $request)
    {
      $categoryData = $request->cat;
      $checkIn = $request->checkin;
      $checkOut = $request->checkout;
      
      if($categoryData === "room"){
        $checkIn = Carbon::parse($request->checkin)->setTime(14, 0, 0);
        $checkOut = Carbon::parse($request->checkout)->setTime(12, 0, 0);
      }

      $bookings = Booking::whereNotIn('status', ['Cancel', 'Full Paid'])
            ->where('facilities_id', $request->facilityId)
            ->select('check_in', 'check_out')
            ->get();

        // Check for overlap
      $occupied = $bookings->contains(function ($booking) use ($checkIn, $checkOut) {
          return !(
              $checkOut <= $booking->check_in ||  // new checkout is before existing check-in
              $checkIn >= $booking->check_out     // new check-in is after existing checkout
          );
      });

      if ($occupied) {
          return response()->json(['Error' => 1, 'Message' => 'Selected dates are already booked for this facility.']);
      }
      $data = [
        'check_in' => $checkIn,
        'check_out' => $checkOut,
      ];
      Booking::where('id', $request->id)->update($data);

      $logData = [
        'user_id' => Auth::id(),
        'action' => 'Update',
        'table_name' => 'Booking',
        'description' => 'Update a Booking',
        'ip_address' => request()->ip(),
        'created_at' => now(),
      ];

      $resultLogs = Log::insert($logData);

      return response()->json(['Error' => 0, 'Message' => 'Successfully update your reservation.']);
    }
    public function ExtendTime(Request $request)
    {
      $id = $request->id;
      $payment = Payment::where('bookings_id',  $id)->first();
      $booking = Booking::where('id', $id)->first();

      $dataPayment = [
        'amount' => $request->NewNumber + $payment->amount,
        'updated_at' => now()
      ];
      $dataBooking = [
        'check_out' => $request->extend,
        'amount' => $request->NewNumber + $booking->amount,
        'updated_at' => now()
      ];

      $logData = [
            'user_id' => Auth::id(),
            'action' => 'Update',
            'table_name' => 'Bookings',
            'description' => 'Extend Time/Date',
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ];
      $resultBooking = Booking::where('id', $id)->update($dataBooking);
      $resultPayment = Payment::where('id', $payment->id)->update($dataPayment);
      Log::insert($logData);

      if($resultPayment && $resultBooking){
        return response()->json(['Error' => 0, 'Message' => 'Successfully extend the Time/Date of your reservation.']);
      }
    }
    public function AddFood($id){

      $decryptedId = $this->decryptNumber($id);
      $bookings = Booking::with('foods')
          ->leftJoin('facilities', 'facilities.id', '=', 'bookings.facilities_id')
          ->leftJoin('users', 'users.id', '=', 'bookings.users_id')
          ->leftJoin('payments', 'payments.bookings_id', '=', 'bookings.id')
          ->leftJoin('person', 'person.id', '=', 'users.person_id')
          ->where('bookings.id', '=', $decryptedId)
          ->get();

      
      $bookings->each(function($booking) {
          $check_in = new DateTime($booking->check_in);
          $check_out = new DateTime($booking->check_out);

          $interval = $check_in->diff($check_out);

          $days = $interval->days;
          if ($interval->h > 0 || $interval->i > 0) {
              $days += 1;
          }

          $booking->day = $days;
      });

      $booking = $bookings->first();
      
      $food_list = Food::leftjoin('food_bookings', 'foods.id', '=', 'food_bookings.foods_id')
        ->leftjoin('bookings', 'food_bookings.bookings_id', '=', 'bookings.id')
        ->where('bookings.id', '=', $decryptedId)
        ->select('foods.*', 'bookings.*', 'food_bookings.*', 'foods.name as food_name', 'bookings.id as bookings_id')
        ->get();
           
      $foods = Food::with('picture')
        ->whereNull('deleted_at')
        ->get();
      
      return view('content.food.add-food', compact('foods', 'booking', 'food_list'));
    }
    public function decryptNumber($input)
    {
        $secretKey = env('APP_ENCRYPT_KEY'); // 32 chars

        $parts = explode(':', $input);
        if (count($parts) !== 2) {
            throw new \Exception("Invalid encrypted input format");
        }

        list($ivHex, $ciphertextHex) = $parts;

        $iv = hex2bin($ivHex);
        $ciphertext = hex2bin($ciphertextHex);

        $plaintext = openssl_decrypt(
            $ciphertext,
            'AES-256-CBC',
            $secretKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($plaintext === false) {
            throw new \Exception("Decryption failed");
        }

        if (!is_numeric($plaintext)) {
            throw new \Exception("Decrypted value is not numeric");
        }

        return $plaintext + 0;
    }
    public function AddFoodProcess(Request $request)
    {
      $id_booking = $request->bookings_id;
      $booking = Booking::where('id', $id_booking)->first();
      $payment = Payment::where('bookings_id',  $id_booking)->first();
      // bookings_id,food_id, food_quantity, total_amount

      $foodIDs = $request->food_id ?? [];
      $foodQuantities = $request->food_quantity ?? [];

      if (is_string($foodIDs)) {
          $foodIDs = explode(',', $foodIDs);
      }
      if (is_string($foodQuantities)) {
          $foodQuantities = explode(',', $foodQuantities);
      }

      foreach ($foodIDs as $index => $id) {
          FoodBooking::insert([
              'bookings_id' => $id_booking,
              'foods_id' => $id,
              'quantity' => $foodQuantities[$index] ?? 1,
              'created_at' => now(),
          ]);
      }

      $dataBooking = [
        'amount' => $booking->amount + $request->total_amount,
        'updated_at' => now()
      ];
      $dataPayment = [
        'amount' => $payment->amount + ($request->total_amount / 2),
        'updated_at' => now()
      ];

      $logData = [
          'user_id' => Auth::id(),
          'action' => 'Update',
          'table_name' => 'Bookings',
          'description' => 'Extend Foods',
          'ip_address' => request()->ip(),
          'created_at' => now(),
      ];
      $resultBooking = Booking::where('id', $booking->id)->update($dataBooking);
      $resultPayment = Payment::where('id', $payment->id)->update($dataPayment);
      Log::insert($logData);
      if($resultPayment && $resultPayment)
      {
        return redirect('/reservations')->with('payment_success', 'Successfully Paid');
      }
    }
    public function AddGuest(Request $request){

      $booking = Booking::where('id', $request->reservation_id)->first();
      $payment = Payment::where('bookings_id',  $request->reservation_id)->first();

      $facilities = Facility::leftjoin('promos', 'promos.facilities_id', '=', 'facilities.id')
                            ->where('facilities.id', '=', $booking->facilities_id)
                            ->select('promos.*', 'facilities.*', 'promos.max_person as promo_max')
                            ->first();

      

      $currentGuest = $booking->guest + $request->guest;
      $maxGuest = $facilities->promo_max == null ? $facilities->max_person + $facilities->limit_add : $facilities->promo_max;
     
      if($currentGuest > $maxGuest){
        return response()->json(['Error' => 1, 'Message' => 'Please reduce occupants to the allowed limit.']);
      }


      $amount = $booking->amount;
      $Additional = 0;
      $over = 0;
      $isTrue = false;
      $totalGuest = 0;

      $totalGuest = $booking->guest + $request->guest;
      $limitGuest = $facilities->promo_max ?? $facilities->max_person;

      if($booking->guest > $limitGuest){
        $over = $booking->guest - $limitGuest;
        $isTrue = true;
      }

      if($totalGuest >  $limitGuest){
        $Additional = $totalGuest - $limitGuest; 

        if($isTrue){
          $Additional -= $over;
        }

        $addAmount = $Additional * $facilities->additional_price;
        $amount += $addAmount;

        $payment->status == 'Partial Payment' ? $paymentData = $amount / 2 : $paymentData = $amount;
        
        $dataPayment = [
          'amount' => $paymentData,
          'updated_at' => now(),
        ];
       
        $resultPayment = Payment::where('id', $payment->id)->update($dataPayment);
      }
      
      $dataBooking = [
        'amount' => $amount,
        'guest' => $booking->guest + $request->guest,
        'updated_at' => now()
      ];

      $logData = [
          'user_id' => Auth::id(),
          'action' => 'Update',
          'table_name' => 'Bookings',
          'description' => 'Add new Guest',
          'ip_address' => request()->ip(),
          'created_at' => now(),
      ];
      $resultBooking = Booking::where('id', $booking->id)->update($dataBooking);
      Log::insert($logData);

      if($resultBooking){
        return response()->json(['Error' => 0, 'Message' => 'Successfully addedd a new guest.']);
      }
    }
    public function PoolManagement(Request $request){
      if(empty($request->totalamountpool)){
        return response()->json(['Error' => 1, 'Message' => 'Guest added to the pool is empty.']);
      }
      $id = Crypt::decryptString($request->id);

      $poolchecking = Pool::where('bookings_id', $id)->first();
      $booking = Booking::where('id', $id)->first();
      $payment = Payment::where('bookings_id', $id)->first();
     
      $paymentData = [
        'amount' => ($payment->amount + $request->totalamountpool)
      ];
      $payment = Payment::where('id', $payment->id)->update($paymentData);
      
      if($poolchecking){
        $pooldata = [
          'adult_count' => $request->adult + $poolchecking->adult_count,
          'children_count' => $request->children + $poolchecking->children_count,
          'total_amount' => $request->totalamountpool + $poolchecking->total_amount,
        ];
        $bookingdata = [
          'amount' => ($booking->amount + $request->totalamountpool),
          'guest_pool' => $request->adult + $request->children + $booking->guest_pool,
        ];
        $bookingresult = Booking::where('id', $id)->update($bookingdata);
        $poolresult = Pool::where('id', $poolchecking->id)->update($pooldata);
      }
      else{
        $pooldata = [
          'bookings_id' => $id,
          'adult_count' => $request->adult,
          'children_count' => $request->children,
          'total_amount' => $request->totalamountpool,
          'created_at' => now()
        ];
        $bookingdata = [
          'amount' => ($booking->amount + $request->totalamountpool),
          'guest_pool' => $request->adult + $request->children
        ];
        $bookingresult = Booking::where('id', $id)->update($bookingdata);
        $poolresult = Pool::insert($pooldata);
      }

      if($poolresult && $bookingresult && $payment){
        return response()->json(['Error' => 0, 'Message' => 'Guest successfully added to the pool.']);
      }
    }
}
