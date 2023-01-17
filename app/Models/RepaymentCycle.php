<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepaymentCycle extends Model
{
    use HasFactory;

    const BIMONTHLY = 'bi_monthly';
    const MONTHLY = 'monthly';
    const CUSTOM = 'custom';
    //
    public function order()
    {
        return $this->hasMany(Order::class);
    }
}
