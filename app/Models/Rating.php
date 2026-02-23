<?php

namespace App\Models;

use App\Models\Facility;
use App\Models\RatingImages;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $table = 'rating';

    protected $fillable = [
        'id',
        'rating',
        'comments',
        'facilities_id',
        'bookings_id',
    ];
    
    public function rating()
    {
        return $this->belongsTo(Facility::class, 'facilities_id');
    }
    public function images()
    {
        return $this->hasMany(RatingImages::class, 'rating_id');
    }
}
