<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderedItem extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $guarded = [];

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function branch(){
        return $this->belongsTo(branch::class);
    }
}
