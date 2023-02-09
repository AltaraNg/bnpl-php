<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\BusinessType;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use App\Models\BnplVendorProduct;
use App\Models\OrderType;
use App\Models\PaymentMethod;
use App\Models\SalesCategory;
use App\Repositories\Eloquent\Repository\OrderRepository;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{

    public function __construct(private readonly OrderRepository $orderRepository)
    {
    }
    public function index(Request $request)
    {
        $user = $request->user();
        $orders = $this->orderRepository->myOrders($user->id);
        return $this->respondSuccess(['orders' => $orders], 'Orders fetched successfully');
    }
    public  function previewAmortization(OrderRequest $orderRequest)
    {
        $orderData = $this->orderData($orderRequest);
        $response =  Http::withHeaders([
            'BNLP-ADMIN-ACCESS' => env('BNLP_ADMIN_ACCESS'),
        ])->post(env('ALTARA_PORTAL_BASE_URL') . '/bnlp/amortization/preview', $orderData);
        if ($response->object()->status !=  'success') {
            if ($response->json('data') && $response->json('data.errors')) {
                return $this->respondCreated(
                    [
                        'message' => $response->json('message'),
                        'errors' => $response->json('data.errors')
                    ],
                    $response->json('message'),
                    422
                );
            }
            return $this->respondError($response->object()->message);
        }
        return $this->respondSuccess(['plans' => $response->object()->data]);
    }

    public function storeOrder(OrderRequest $orderRequest)
    {
        $orderData = $this->orderData($orderRequest);
        $response =  Http::withHeaders([
            'BNLP-ADMIN-ACCESS' => env('BNLP_ADMIN_ACCESS'),
        ])->post(env('ALTARA_PORTAL_BASE_URL') . '/bnlp/create/order', $orderData);

        if ($response->object()->status !=  'success') {
            if ($response->json('data') && $response->json('data.errors')) {
                return $this->respondCreated(
                    [
                        'message' => $response->json('message'),
                        'errors' => $response->json('data.errors')
                    ],
                    $response->json('message'),
                    422
                );
            }
            return $this->respondError($response->object()->message);
        }
        return $this->respondSuccess(['order' => $response->object()->data]);
    }

    public function orderData(OrderRequest $orderRequest): array
    {
        $businessType = BusinessType::query()->where('slug', 'ap_products')->first();
        $orderType = OrderType::query()->where('name', 'Altara Pay')->first();
        $paymentMethod = PaymentMethod::query()->where('name', 'direct-debit')->first();
        $saleCategory = SalesCategory::query()->first();
        $product = BnplVendorProduct::query()->updateOrCreate(
            [
                'name' => $orderRequest->product_name,
                'vendor_id' => $orderRequest->user()->id,
            ],
            ['price' => $orderRequest->product_price]
        );
        return [
            "bnpl_vendor_product_id" => $product->id,
            "customer_id" => $orderRequest->customer_id,
            "bank_id" => 1,
            "business_type_id" => $businessType->id,
            "owner_id" => $orderRequest->user()->id,
            "inventory_id" => 2,
            "payment_method_id" => $paymentMethod->id,
            "payment_gateway_id" => 1,
            "order_type_id" => $orderType->id,
            "sales_category_id" => $saleCategory->id,
            "repayment_cycle_id" => $orderRequest->repayment_cycle_id,
            "repayment_duration_id" => $orderRequest->repayment_duration_id,
            "repayment" => $orderRequest->repayment,
            "down_payment" => $orderRequest->down_payment,
            "financed_by" => Order::ALTARA_BNPL,
            "product_price" => $orderRequest->product_price,
        ];
    }

    public function fetchProducts(Request $request)
    {
        $productsQuery = BnplVendorProduct::query()->where('status', true)->where('vendor_id', $request->user()->id);
        if (strlen($request->query('product_name')) > 0) {
            $productsQuery =   $productsQuery->where('name', 'LIKE', '%' . $request->query('product_name') . '%');
        }
        return $this->respondSuccess(['products' => $productsQuery->simplePaginate()], 'Products fetched');
    }
}
