<?php

namespace App\Http\Controllers;

use App\Models\BnplVendorProduct;
use App\Models\Commission;
use App\Models\MerchantCommission;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $orderQuery = Order::query()->where('owner_id', auth()->id())->where('financed_by', 'altara-bnpl');
        $total_number_of_sales = $orderQuery->count();
        $total_revenue = BnplVendorProduct::query()->where('vendor_id', auth()->id())->sum('price');


        $totalCommission = MerchantCommission::where('merchant_id', auth()->id())->sum('amount');

        $recent_activities = $orderQuery->with('bnplProduct')->latest('created_at')->paginate(request('per_page', 15));
        return $this->respondSuccess(['recent_activities' => $recent_activities, 'total_number_of_sales' => $total_number_of_sales, 'total_revenue' => $total_revenue, 'total_commission' => $totalCommission], 'Data fetched successfully');
    }
}
