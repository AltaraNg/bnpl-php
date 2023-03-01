<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BnplVendorProduct extends Model
{
    use HasFactory;
    protected $table = 'bnpl_vendor_products';
    protected $guarded = [];
}
