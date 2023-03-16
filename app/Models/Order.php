<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;
    protected $with = ['amortizations'];

    protected $table= 'new_orders';

    const ALTARA_BNPL ='altara-bnpl';
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function bnplProduct(): BelongsTo
    {
        return $this->belongsTo(BnplVendorProduct::class, 'bnpl_vendor_product_id');
    }

    public function amortizations(): HasMany
    {
        return $this->hasMany(Amortization::class, 'new_order_id');
    }
    public function downPaymentRate()
    {
        return $this->belongsTo(DownPaymentRate::class, 'down_payment_rate_id');
    }
    public function orderType()
    {
        return $this->belongsTo(OrderType::class, 'order_type_id');
    }
    public function repaymentDuration()
    {
        return $this->belongsTo(RepaymentDuration::class);
    }
    public function businessType()
    {
        return $this->belongsTo(BusinessType::class);
    }
    public function paymentMethod()
    {
        return $this->hasOne(PaymentMethod::class);
    }
    public function salesCategory()
    {
        return $this->belongsTo(SalesCategory::class, 'sales_category_id');
    }
    public function repaymentCycle()
    {
        return $this->belongsTo(RepaymentCycle::class);
    }

    public function lateFee()
    {
        return $this->hasMany(LateFee::class, 'order_id');
    }
}
