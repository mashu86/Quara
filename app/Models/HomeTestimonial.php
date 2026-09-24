<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeTestimonial extends Model
{
    protected $fillable = ['customer_name', 'review', 'rating', 'sort_order', 'status'];
}
