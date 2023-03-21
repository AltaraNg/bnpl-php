<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $orderQuery = Order::query()->where('owner_id', auth()->id())->where('financed_by', 'altara-bnpl');
        $total_number_of_sales = $orderQuery->count();
        $total_revenue =  $orderQuery->sum('product_price');

        $recent_activities = $orderQuery->with('bnplProduct', 'customer')->latest('created_at')->limit(10)->get();
        return $this->respondSuccess(['recent_activities' => $recent_activities, 'total_number_of_sales' => $total_number_of_sales, 'total_revenue' => $total_revenue], 'Data fetched successfully');
    }
}
