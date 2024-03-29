<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Customer extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $with = ['guarantors', 'latestCreditCheckerVerifications'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id')->latest();
    }

    public function verification(): HasOne
    {
        return $this->hasOne(Verification::class)->withDefault();
    }

    public function creditCheckerVerifications()
    {
        return $this->hasMany(CreditCheckerVerification::class, 'customer_id');
    }

    public function latestCreditCheckerVerifications()
    {
        return $this->hasOne(CreditCheckerVerification::class, 'customer_id')->latestOfMany();
    }

    public function guarantors()
    {
        return $this->hasMany(Guarantor::class, 'customer_id');
    }

    public function merchants()
    {
        return $this->belongsToMany(User::class, 'merchant_customer', 'customer_id', 'user_id');
    }

    
    public function newDocuments(): MorphMany
    {
        return $this->morphMany(NewDocument::class, 'documentable');
    }
}
