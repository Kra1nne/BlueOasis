<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pool extends Model
{
    protected $table = 'pool';

    protected $fillable = [ 
        'id',
        'bookings_id',
        'adult_count',
        'children_count',
        'total_amount',
        'created_at'
    ];
}
