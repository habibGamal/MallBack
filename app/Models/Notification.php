<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getCreatedAtAttribute($created_at){
        return Carbon::parse($created_at)->diffForHumans();
    }

    public function branch(){
        return $this->belongsTo(Branch::class,'r_branch_id');
    }

    public function sBranch(){
        return $this->belongsTo(Branch::class,'s_branch_id');
    }
}
