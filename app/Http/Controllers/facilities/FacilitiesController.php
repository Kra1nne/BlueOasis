<?php

namespace App\Http\Controllers\facilities;

use App\Models\Log;
use App\Models\Picture;
use App\Models\Facility;
use App\Models\Rating;
use App\Models\Booking;
use App\Models\Food;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;


class FacilitiesController extends Controller
{
  public function index(Request $request) {
    $venues = Facility::with('picture')
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
    
    return view('content.facilities.facilities', compact('venues', 'isFiltering'));
  }
  // Add
  public function store(Request $request) {

    $logData = [
      'user_id' => Auth::id(),
      'action' => 'Add',
      'table_name' => 'Facilities',
      'description' => 'Added a facility',
      'ip_address' => request()->ip(),
      'created_at' => now(),
    ];

    $resultLogs = Log::insert($logData);

    $facility = Facility::create([
        'name' => $request->name,
        'description' => $request->description,
        'price' => $request->price,
        'category' => $request->category,
        'created_at' => now(),
        'updated_at' => now(),
        'users_id' => Auth::id(),
        'max_person' => $request->max_person,
        'additional_price' => $request->additional_price,
        'amenities' => $request->amenities,
        'additional_price_time' => $request->addprice_perhour,
        'limit_add' => $request->limit_add,
    ]);

    if ($request->hasFile('imagesData')) {
      foreach ($request->file('imagesData') as $image) {
          $path = $image->store('public/uploads');
            Picture::create([
                'path' => Storage::url($path),
                'facilities_id' => $facility->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    return response()->json(['Error' => 0, 'Message' => 'Successfully added a data']);
  }
  // Delete
  public function delete(Request $request){

    $logData = [
      'user_id' => Auth::id(),
      'action' => 'Delete',
      'table_name' => 'Facilities',
      'description' => 'Delete a facility',
      'ip_address' => request()->ip(),
      'created_at' => now(),
    ];

    $resultLogs = Log::insert($logData);

    $facility = [
      'deleted_at' => now()
    ];

    $resultvenue = Facility::where('id', Crypt::decryptString($request->id))->update($facility);
    $venueImages = Picture::where('facilities_id', Crypt::decryptString($request->id))->get();
    foreach ($venueImages as $image) {
        $filePath = str_replace('storage/', 'public/', $image->path);
        Storage::delete($filePath);
    }
    Picture::where('facilities_id', Crypt::decryptString($request->id))->delete();

    if($resultvenue){
      return response()->json(['Error' => 0, 'Message' => 'Successfully delete a data']);
    }
  }
  // Edit
  public function update(Request $request) {

    if ($request->has('removed_images')) {

        foreach ($request->removed_images as $image) {
            if (!empty($image)) {
              $filePath = str_replace('storage/', 'public/', $image);
              Storage::delete($filePath);
              Picture::where('path', $image)->delete();
            }
        }
    }


    $logData = [
      'user_id' => Auth::id(),
      'action' => 'Update',
      'table_name' => 'Facilities',
      'description' => 'Update the Facilities',
      'ip_address' => request()->ip(),
      'created_at' => now(),
    ];

    $resultLogs = Log::insert($logData);

    $facility = [
        'name' => $request->name,
        'description' => $request->description,
        'price' => $request->price,
        'category' => $request->category,
        'created_at' => now(),
        'updated_at' => now(),
        'max_person' => $request->max_person,
        'additional_price' => $request->additional_price,
        'amenities' => $request->amenities,
        'additional_price_time' => $request->addprice_perhour,
        'limit_add' => $request->limit_add,
    ];

    if ($request->hasFile('imagesData')) {
      foreach ($request->file('imagesData') as $image) {
          $path = $image->store('public/uploads');
            Picture::create([
                'path' => Storage::url($path),
                'created_at' => now(),
                'updated_at' => now(),
                'facilities_id' => Crypt::decryptString($request->id),
            ]);
        }
    }
    $resultUser = Facility::where('id', Crypt::decryptString($request->id))->update($facility);

    if($resultUser){
      return response()->json(['Error' => 0, 'Message' => 'Successfully update a data']);
    }
  }
  public function viewDetail($id){
    $decryptedId = Crypt::decryptString($id);
      $venue = Facility::with('picture', 'promo', 'rating')
        ->where('id', $decryptedId)
        ->whereNull('deleted_at')
        ->first();
      
      $ratings = Rating::leftjoin('bookings', 'bookings.id', '=', 'rating.bookings_id')
        ->leftjoin('facilities', 'facilities.id', '=', 'rating.facilities_id')
        ->leftjoin('users', 'users.id', '=', 'bookings.users_id')
        ->leftjoin('person', 'person.id', '=', 'users.person_id')
        ->where('facilities.id', $decryptedId)
        ->select('*','rating.created_at as CommentDate')
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
        return view('content.booking.facility_details_admin', compact('venue', 'ratings','bookingDetails'));
  }
  public function viewFoods(Request $request){

      if (empty($request->all())) {
        return redirect()->route('booking')->with('error', 'No food items selected.');
      }
      $bookingDetails = $request->all();

      $foods = Food::with('picture')
        ->whereNull('deleted_at')
        ->get();


      return view('content.booking.food_details_admin', compact('bookingDetails', 'foods'));
    }
}
