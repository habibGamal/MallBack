<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function sub_categories(){
        return $this->hasMany(Category::class,'parent_id');
    }
    public function parent_category(){
        return $this->belongsTo(Category::class,'parent_id');
    }
    public function products(){
        return $this->hasMany(Product::class, 'category_id');
    }
}
