<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function category(){
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function options(){
        return $this->hasMany(Option::class);
    }

    public function carts(){
        return $this->belongsToMany(Cart::class)->withPivot(['product_count']);
    }

    public function branches(){
        return $this->belongsToMany(Branch::class);
    }

    public function orderedItem(){
        return $this->hasOne(OrderedItem::class);
    }
}
