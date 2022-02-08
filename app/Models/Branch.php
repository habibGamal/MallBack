<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class);
    }
}
