<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
  protected $table = 'bookings';

  protected $fillable = [
    'id',
    'reserve',
    'walk_in',
    'status',
    'date',
    'guest',
    'facilities_id',
    'users_id',
    'promos_id',
    'amount',
    'check_in',
    'check_out',
    'created_at',
    'updated_at',
    'guest',
    'facility_income',
    'name',
    'guest_pool'
  ];

  public function foods()
  {
    return $this->belongsToMany(Food::class, 'food_bookings', 'bookings_id', 'foods_id')->withPivot('quantity');
  }
  public function facility()
  {
    return $this->belongsTo(Facility::class, 'facilities_id');
  }

}
