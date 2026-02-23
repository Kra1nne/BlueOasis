<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatingImages extends Model
{
    protected $table = 'rating_images';

    protected $fillable = [
        'id',
        'rating_id',
        'path',
        'uploaded_at',
    ];
}
