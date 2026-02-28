<?php

namespace App\Http\Controllers\payment;

use App\Models\Log;
use App\Models\Food;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Facility;
use App\Models\FoodBooking;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class PaymentController extends Controller
{
    public function checkout(Request $request)
    {
        // Store booking data temporarily
        session(['payment_data' => $request->all()]);

        // Determine payment type
        $paymentType = $request->input('payment_type'); // 'partial' or 'full'

        // Calculate amount
        $totalAmount = (int) $request->input('total_amount');
        if ($paymentType === 'partial') {
            $amount = (int) ($totalAmount * 100 / 2); 
        } else {
            $amount = $totalAmount * 100; 
        }

        // Get facility name
        $facilityName = Facility::where('id', $request->facility_id)->first();
        $name = $facilityName->name ?? "Cottages/Rooms";

        // Create Checkout Session
        $session = $this->createCheckoutSession($amount, $name);

        if ($session["status"] === "timeout" || $session["status"] === "error") {
            return back()->with('payment_error', $session["message"]);
        }

        return redirect()->away($session["url"]);
    }

    // new payment
    public function fullpaid(Request $request){        
        $paymantData = Payment::where('bookings_id', $request->id)->first();
        $bookingData = Booking::where('id', $request->id)->first();
        $facilityName = Facility::where('id', $bookingData->facilities_id)->first();
        $name = $facilityName->name ?? "Cottages/Rooms";
        $amount = $paymantData->amount * 100;
        session(['data' => $request->id]);

        $session = $this->createCheckoutSession_V2($amount, $name);

        if ($session["status"] === "timeout" || $session["status"] === "error") {
            return back()->with('payment_error', $session["message"]);
        }

        return redirect()->away($session["url"]);
    }
    private function createCheckoutSession_V2($amount, $name)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.paymongo.com/v1/checkout_sessions",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_TIMEOUT => 8,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "content-type: application/json",
                "authorization: Basic " . base64_encode(config('services.paymongo.secret') . ":")
            ],
            CURLOPT_POSTFIELDS => json_encode([
                "data" => [
                    "attributes" => [
                        "line_items" => [
                            [
                                "currency" => "PHP",
                                "amount" => $amount,
                                "description" => "Blue Oasis Booking Payment",
                                "name" => $name,
                                "quantity" => 1
                            ]
                        ],
                        "payment_method_types" => ["gcash"],
                        "send_email_receipt" => false,
                        "show_description" => true,
                        "show_line_items" => true,
                        "description" => "Blue Oasis Payment",
                        "success_url" => route('reservation-paid.success'),
                        "cancel_url" => route('user-reservation-list'),
                    ]
                ]
            ]),
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl); 
        curl_close($curl);

        if ($err) {
            return [
                "status" => "timeout",
                "message" => "Payment gateway is slow or not responding."
            ];
        }

        $decoded = json_decode($response, true);

        if (!isset($decoded["data"]["attributes"]["checkout_url"])) {
            return [
                "status" => "error",
                "message" => "Unable to generate checkout URL."
            ];
        }

        return [
            "status" => "ok",
            "url" => $decoded["data"]["attributes"]["checkout_url"]
        ];
    }
    public function fullyPaidSuccess(Request $request){
        $data = session('data');
        $id = (int)$data;

        $booking = Booking::where('id', $id)->first();
        $payment = Payment::where('bookings_id',  $id)->first();

        $bookingsData = [
            'status' => "Fully Paid",
            'updated_at' => now()
        ];
        Booking::where('id', $id)->update($bookingsData);

        $paymentData = [
            'status' => "Fully Paid",
            'updated_at' => now(),
            'amount' => $booking->amount,
        ];
        Payment::where('id', $payment->id)->update($paymentData);
        $logData = [
            'user_id' => Auth::id(),
            'action' => 'Update',
            'table_name' => 'Bookings',
            'description' => 'Fully Paid',
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ];
        $logs = Log::insert($logData);

        if($logs){
            return redirect('/reservations-list')->with('payment_success', 'Successfully Paid');
        }
    }

    // new payment

    private function createCheckoutSession($amount, $name)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.paymongo.com/v1/checkout_sessions",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_TIMEOUT => 8,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "content-type: application/json",
                "authorization: Basic " . base64_encode(config('services.paymongo.secret') . ":")
            ],
            CURLOPT_POSTFIELDS => json_encode([
                "data" => [
                    "attributes" => [
                        "line_items" => [
                            [
                                "currency" => "PHP",
                                "amount" => $amount,
                                "description" => "Blue Oasis Booking Payment",
                                "name" => $name,
                                "quantity" => 1
                            ]
                        ],
                        "payment_method_types" => ["gcash"],
                        "send_email_receipt" => false,
                        "show_description" => true,
                        "show_line_items" => true,
                        "description" => "Blue Oasis Payment",
                        "success_url" => route('payment.success'),
                        "cancel_url" => route('booking'),
                    ]
                ]
            ]),
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl); 
        curl_close($curl);

        if ($err) {
            return [
                "status" => "timeout",
                "message" => "Payment gateway is slow or not responding."
            ];
        }

        $decoded = json_decode($response, true);

        if (!isset($decoded["data"]["attributes"]["checkout_url"])) {
            return [
                "status" => "error",
                "message" => "Unable to generate checkout URL."
            ];
        }

        return [
            "status" => "ok",
            "url" => $decoded["data"]["attributes"]["checkout_url"]
        ];
    }
    public function paymentSuccess(Request $request)
    {
        $data = session('payment_data');

        $paymentType = $data['payment_type'] ?? 'partial'; 

        $totalAmount = (float) $data['total_amount'];
        if ($paymentType === 'partial') {
            $paymentAmount = $totalAmount / 2;
            $paymentStatus = 'Partial Payment';
        } else {
            $paymentAmount = $totalAmount;
            $paymentStatus = 'Fully Paid';
        }

        $checkIn = $data['check_in'];
        $checkOut = $data['check_out'];

        // Keep the FIRST time only
        if (substr_count($checkIn, 'T') > 1) {
            $parts = explode('T', $checkIn);
            $checkIn = $parts[0] . 'T' . $parts[1];
        }

        if (substr_count($checkOut, 'T') > 1) {
            $parts = explode('T', $checkOut);
            $checkOut = $parts[0] . 'T' . $parts[1];
        }

        $booking = [
            'reserve' => 1,
            'walk_in' => 0,
            'status' => $paymentStatus,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'created_at' => now(),
            'users_id' => Auth::id(),
            'promos_id' => $data['promo_id'] ?? null,
            'amount' => $data['total_amount'],
            'facilities_id' => $data['facility_id'],
            'guest' => $data['guest_count'],
            'facility_income' => $data['facility_income'],
        ];
        $bookingID = Booking::create($booking);

        $foodIDs = $data['food_id'] ?? [];
        $foodQuantities = $data['food_quantity'] ?? [];

        if (is_string($foodIDs)) {
            $foodIDs = explode(',', $foodIDs);
        }
        if (is_string($foodQuantities)) {
            $foodQuantities = explode(',', $foodQuantities);
        }

        foreach ($foodIDs as $index => $id) {
            FoodBooking::insert([
                'bookings_id' => $bookingID->id,
                'foods_id' => $id,
                'quantity' => $foodQuantities[$index] ?? 1,
                'created_at' => now(),
            ]);
        }

        $payment = [
            'status' => $paymentStatus,
            'amount' => $paymentAmount,
            'created_at' => now(),
            'bookings_id' => $bookingID->id,
        ];
        Payment::insert($payment);

        session()->forget('payment_data');
        return redirect()->route('booking')->with('show_modal', true)->with('booking_id', Crypt::encryptString($bookingID->id));
    }
    public function paymentFailed(Request $request)
    {
        return view('payment.failed');
    }

    public function displayPDF(Request $request) {
        $Id = $request->query('booking');
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

        $bookingDetails = [
            'name' => $booking->name ?? $booking->facility_name,
            'price' => $booking->price ?? $booking->facility_price,
            'check_in' => $booking->check_in,
            'check_out' => $booking->check_out,
            'date' => now(),
            'promo' => $booking->name,
            'status' => $booking->status,
            'service_fee' => 0,
            'total_amount' => $booking->amount,
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

    public function paymentSuccessAdmin(Request $request)
    {
        $paymentType = $request->payment_type ?? 'partial'; // fallback to partial

        $totalAmount = (float) $request->total_amount;
        if ($paymentType === 'partial') {
            $paymentAmount = $totalAmount / 2;
            $paymentStatus = 'Partial Payment';
        } else {
            $paymentAmount = $totalAmount;
            $paymentStatus = 'Fully Paid';
        }
        
        $booking = [
            'reserve' => 1,
            'walk_in' => 1,
            'status' => $paymentStatus,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'created_at' => now(),
            'users_id' => Auth::id(),
            'promos_id' => $request->promo_id ?? null,
            'amount' => $request->total_amount,
            'facilities_id' => $request->facility_id,
            'guest' => $request->guest_count,
            'facility_income' => $request->facility_income,
            'name' => $request->name
        ];
        $bookingID = Booking::create($booking);

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
                'bookings_id' => $bookingID->id,
                'foods_id' => $id,
                'quantity' => $foodQuantities[$index] ?? 1,
                'created_at' => now(),
            ]);
        }

        $payment = [
            'status' => $paymentStatus,
            'amount' => $paymentAmount,
            'created_at' => now(),
            'bookings_id' => $bookingID->id,
        ];
        Payment::insert($payment);

        return redirect()->route('product-facilities')->with('show_modal', true)->with('booking_id', Crypt::encryptString($bookingID->id));
    }
    
}
