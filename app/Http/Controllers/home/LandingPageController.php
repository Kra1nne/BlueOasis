<?php

namespace App\Http\Controllers\home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rating;

class LandingPageController extends Controller
{
    public function index(){
      $rating = Rating::avg('rating');
      $count = Rating::count();

      $ratingData = Rating::leftjoin('bookings', 'bookings.id', "=", 'rating.bookings_id')
                        ->leftjoin('users', 'users.id', '=', 'bookings.users_id')
                        ->leftjoin('person', 'users.person_id', '=', 'person.id')
                        ->whereNull('rating.deleted_at')
                        ->orderBy('rating.created_at', 'Desc')
                        ->limit(3)
                        ->get();
    
      $exploreRooms = [
            [
                'title' => 'Attic Room',
                'description' => 'Experience the beauty of the ocean from your private suite with a balcony and modern furnishings',
                'aminities' => [
                    'Pool Access',
                    'Ocean View',
                    'Private Balcony'
                ],
                'images' => [
                    asset('assets/img/elements/Room1-1.jpg'),
                    asset('assets/img/elements/Room2.jpg')
                ]
            ],
            [
                'title' => 'Moynihan House',
                'description' => 'A spacious room with stunning views, and a peaceful atmosphere.',
                 'aminities' => [
                    'Pool Access',
                    'Air-Conditioned',
                    'Private Bathroom',
                    'Kitchen',
                    'Refrigirator',
                    'Ocean View',
                    'Private Balcony'
                ],
                'images' => [
                    asset('assets/img/elements/Room2-1.jpg'),
                    asset('assets/img/elements/Room1.jpg'),
                    asset('assets/img/elements/Room2-2.jpg'),
                    asset('assets/img/elements/Room2-3.jpg')
                ]
            ],
            [
                'title' => 'Family Inn',
                'description' => 'A spacious room with stunning views of the pool and sea, perfect for families seeking comfort and relaxation.',
                 'aminities' => [
                    'Pool Access',
                    'Air-Conditioned',
                    'Ocean View',
                    'Private Balcony'
                ],
                'images' => [
                    asset('assets/img/elements/Room3-1.jpg'),
                    asset('assets/img/elements/Room3.jpg')
                ]
            ],
        ];

      return view('content.home.landingpage', compact('exploreRooms', 'rating', 'count', 'ratingData'));
    }
}
