<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $orderQuery = Order::query()->where('owner_id', auth()->id())->where('financed_by', 'altara-bnpl');
        $total_number_of_sales = $orderQuery->count();
        $total_revenue =  $orderQuery->sum('product_price');


        $totalCommission = DB::table('merchant_commissions')
        ->join('commissions', 'merchant_commissions.commission_id', '=', 'commissions.id')
        ->join('bnpl_vendor_products', 'merchant_commissions.bnpl_vendor_product_id', '=', 'bnpl_vendor_products.id')
        ->where('merchant_commissions.merchant_id', request()->user()->id)
        ->sum(DB::raw('(commissions.value / 100) * bnpl_vendor_products.price'));

        $recent_activities = $orderQuery->with('bnplProduct')->latest('created_at')->limit(10)->get();
        return $this->respondSuccess(['recent_activities' => $recent_activities, 'total_number_of_sales' => $total_number_of_sales, 'total_revenue' => $total_revenue, 'total_commission' => $totalCommission], 'Data fetched successfully');
    }
}
