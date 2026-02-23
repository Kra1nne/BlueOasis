<?php

namespace App\Http\Controllers\feedback;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rating;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = Rating::leftjoin('facilities', 'facilities.id', '=', 'rating.facilities_id')
                        ->leftjoin('users', 'users.id', '=', 'facilities.users_id')
                        ->leftjoin('person', 'person.id', '=', 'users.person_id')
                        ->orderBy('rating.created_at', 'desc')
                        ->select('*','rating.created_at as created_at')
                        ->get(); 
                        
        return view('content.feedback.feedback-list', compact('feedbacks'));
    }
}
