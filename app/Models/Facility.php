<?php

namespace App\Models;

use App\Models\Promo;
use App\Models\Picture;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{

  protected $table = 'facilities';

  protected $fillable = [
    'id',
    'name',
    'description',
    'discount',
    'price',
    'category',
    'amenities',
    'created_at',
    'updated_at',
    'deleted_at',
    'users_id',
    'max_person',
    'additional_price',
    'additional_price_time',
    'limit_add'
  ];
  public function picture()
  {
      return $this->hasMany(Picture::class, 'facilities_id');
  }
  public function promo()
  {
      return $this->hasMany(Promo::class, 'facilities_id');
  }

  public function rating()
  {
    return $this->hasMany(Rating::class, 'facilities_id');
  }

  public function bookings()
  {
    return $this->hasMany(Booking::class, 'facilities_id');
  }
}
