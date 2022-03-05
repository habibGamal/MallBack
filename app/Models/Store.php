<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function admin(){
        return $this->belongsTo(Admin::class);
    }

    public function branches(){
        return $this->hasMany(Branch::class);
    }

    public function orderedItems(){
        return $this->hasManyThrough(OrderedItem::class,Branch::class);
    }
}
