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

    public function orderedItems(){
        return $this->hasMany(OrderedItem::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class,'ordered_items');
    }

    public function notifications(){
        return $this->hasMany(Notification::class,'r_branch_id');
    }
}
