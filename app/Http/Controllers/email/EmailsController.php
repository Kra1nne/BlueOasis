<?php

namespace App\Http\Controllers\email;

use App\Models\User;
use App\Mail\SuccessMail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;

class EmailsController extends Controller
{
    public function successEmail(Request $request)
    {
        $bookingId = Crypt::decryptString($request->booking_id);

        $id = Booking::where('id', $bookingId)->first();
        $booking = User::where('id', $id->users_id)->first();

        if (!$booking) {
            return response()->json(['status' => 'error'], 400);
        }

        Mail::to($booking->email)->send(new SuccessMail());

        return response()->json(['status' => 'sent']);
    }
}
