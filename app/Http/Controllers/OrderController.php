<?php

namespace App\Http\Controllers;

use App\DataTransferObject\GuarantorDto;
use App\Models\Order;
use App\Models\BusinessType;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use App\Models\BnplVendorProduct;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\Guarantor;
use App\Models\OrderType;
use App\Models\PaymentMethod;
use App\Models\SalesCategory;
use App\Repositories\Eloquent\Repository\OrderRepository;
use Illuminate\Support\Facades\DB;
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
        $orderId = $response->object()->data->order->id;
        $productId = $response->object()->data->order->bnpl_vendor_product_id;
        $commission = null;
        if ($orderRequest->input('has_document')  == 'yes') {
            $commission = Commission::query()->where('name', '5_percent')->first();
        }
        if ($orderRequest->input('has_document')  == 'no') {
            $commission = Commission::query()->where('name', '2_percent')->first();
        }

        if ($commission &&  $orderRequest->cost_price) {
            DB::table('merchant_commissions')->insert([
                'commission_id' => $commission->id,
                'merchant_id' => $orderRequest->user()->id,
                'bnpl_vendor_product_id' => $productId,
                'new_order_id' => $orderId,
                'amount' => ($commission->value / 100) * $orderRequest->cost_price,
            ]);
        }
        return $this->respondSuccess(['order' => $response->object()->data]);
    }

    public function orderData(OrderRequest $orderRequest): array
    {
        // $businessType = BusinessType::query()->where('slug', 'ap_products')->first();
        $orderType = OrderType::query()->where('name', 'Altara Pay')->first();
        $paymentMethod = PaymentMethod::query()->where('name', 'direct-debit')->first();
        $saleCategory = SalesCategory::query()->first();
        $product = BnplVendorProduct::query()->updateOrCreate(
            [
                'name' => $orderRequest->product_name,
                'vendor_id' => $orderRequest->user()->id,
            ],
            ['price' => $orderRequest->cost_price]
        );
        $customer = Customer::where('id', $orderRequest->customer_id)->first();
        if ($customer) {
            $customer->merchants()->syncWithoutDetaching([$orderRequest->user()->id]);
        }
        return [
            "bnpl_vendor_product_id" => $product->id,
            'business_type_id'=> $orderRequest->business_type_id,
            "customer_id" => $orderRequest->customer_id,
            "bank_id" => 1,
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
            "fixed_repayment" => $orderRequest->fixed_repayment,
            "cost_price" => $orderRequest->cost_price
        ];
    }

    public function fetchProducts(Request $request)
    {
        $productsQuery = BnplVendorProduct::query()->where('status', true)->where('vendor_id', $request->user()->id);
        if (strlen($request->query('product_name')) > 0) {
            $productsQuery =   $productsQuery->where('name', 'LIKE', '%' . $request->query('product_name') . '%');
        }
        return $this->respondSuccess(['products' => $productsQuery->paginate(request('per_page'))], 'Products fetched');
    }
}
