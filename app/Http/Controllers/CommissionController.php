<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\MerchantCommission;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index()
    {
        $commissions =  MerchantCommission::query()->where('merchant_id', request()->user()->id)
        ->with('product', 'commission')
        ->paginate(request('per_page', 15));
        return $this->respondSuccess(['commissions' => $commissions], 'Fetched commissions');
    }
}
