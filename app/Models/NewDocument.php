<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewDocument extends Model
{
    use HasFactory, SoftDeletes;

    public function documentable(){
        return $this->morphTo();
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
