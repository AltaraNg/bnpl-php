<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\BnplVendorProduct;
use App\Models\CreditCheckerVerification;
use App\Models\Customer;
use App\Notifications\PendingCreditCheckNotification;
use App\Repositories\Eloquent\Repository\CustomerRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class CreditCheckerVerificationController extends Controller
{
    public function __construct(private readonly CustomerRepository $customerRepository)
    {
    }
    public function index(Request $request)
    {
    }

    public function store(OrderRequest $request)
    {

        $customer = $this->customerRepository->findById($request->input('customer_id'));
        /** @var User $vendor */
        $vendor = auth()->user();

        $product = BnplVendorProduct::query()->updateOrCreate(
            [
                'name' => $request->product_name,
                'vendor_id' => $vendor->id,
            ],
            ['price' => $request->product_price]
        );
        if ($customer->creditCheckerVerifications()->where('status', CreditCheckerVerification::PENDING)->exists()) {
            $creditCheckerVerification = $customer->latestCreditCheckerVerifications()->where('status', CreditCheckerVerification::PENDING)->first();
        } else {
            $creditCheckerVerification = CreditCheckerVerification::create([
                'customer_id' => $request->input('customer_id'),
                'initiated_by' => request()->id(),
                'bnpl_vendor_product_id' => $product->id,
            ]);
        }
        Notification::route('mail', config('app.credit_checker_mail'))->notify(new PendingCreditCheckNotification($customer, $vendor, $product, $creditCheckerVerification));
        return $this->respondSuccess(['credit_check_verification' =>  $creditCheckerVerification], 'Credit check initiated and notification sent');
    }

    public function verifyCreditCheck(CreditCheckerVerification $creditCheckerVerification)
    {
        /** @var User $vendor */
        $vendor = auth()->user();
        if ($creditCheckerVerification->status === CreditCheckerVerification::PENDING) {
            Notification::route('mail', config('app.credit_checker_mail'))->notify(new PendingCreditCheckNotification($creditCheckerVerification->customer, $creditCheckerVerification->vendor, $creditCheckerVerification->product, $creditCheckerVerification));
            return $this->respondSuccess(['status' => CreditCheckerVerification::PENDING], 'Credit check still pending, please check again in 5 minutes');
        }
        if ($creditCheckerVerification->status === CreditCheckerVerification::FAILED) {
            return $this->respondSuccess(['status' => CreditCheckerVerification::FAILED], 'Credit Check failed');
        }
        if ($creditCheckerVerification->status === CreditCheckerVerification::PASSED) {
            return $this->respondSuccess(['status' => CreditCheckerVerification::PASSED], 'Credit check has been passed');
        }
       
    }
}
