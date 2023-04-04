<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantCommission extends Model
{
    use HasFactory;
    protected $table =  'merchant_commissions';

    public function product()
    {
        return $this->belongsTo(BnplVendorProduct::class, 'bnpl_vendor_product_id');
    }

    public function merchant()
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }

    public function commission()
    {
        return $this->belongsTo(Commission::class, 'commission_id');
    }
}
