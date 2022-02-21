<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderedItems()
    {
        return $this->hasMany(OrderedItem::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class,'ordered_items');
    }
}
