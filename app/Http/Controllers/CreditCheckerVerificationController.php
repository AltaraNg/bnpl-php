<?php

namespace App\Http\Controllers;

use App\DataTransferObject\GuarantorDto;
use App\Http\Requests\OrderRequest;
use App\Models\BnplVendorProduct;
use App\Models\CreditCheckerVerification;
use App\Models\Customer;
use App\Models\Guarantor;
use App\Models\NewDocument;
use App\Notifications\PendingCreditCheckNotification;
use App\Repositories\Eloquent\Repository\CustomerRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        try {
            $customer = $this->customerRepository->findById($request->input('customer_id'));

           

            $guarantors  = GuarantorDto::fromOrderApiRequest($request);
            foreach ($guarantors as $key => $guarantor) {
                $guarantorDto  = GuarantorDto::fromSelf($guarantor);
                Guarantor::query()->updateOrCreate(
                    [
                        'customer_id' => $guarantorDto->customer_id,
                        'phone_number' => $guarantorDto->phone_number
                    ],
                    [
                        'first_name' => $guarantorDto->first_name,
                        'last_name' => $guarantorDto->last_name,
                        'home_address' => $guarantorDto->home_address,
                    ]
                );
            }
            /** @var User $vendor */
            $vendor = auth()->user();

            $product = BnplVendorProduct::query()->updateOrCreate(
                [
                    'name' => $request->product_name,
                    'vendor_id' => $vendor->id,
                ],
                ['price' => $request->cost_price]
            );
            if ($customer->creditCheckerVerifications()->where('status', CreditCheckerVerification::PENDING)->exists()) {
                $creditCheckerVerification = $customer->latestCreditCheckerVerifications()->where('status', CreditCheckerVerification::PENDING)->first();
            } else {
                $creditCheckerVerification = CreditCheckerVerification::create([
                    'customer_id' => $request->input('customer_id'),
                    'initiated_by' => request()->user()->id,
                    'bnpl_vendor_product_id' => $product->id,
                    'repayment_duration_id' => $request->input('repayment_duration_id'),
                    'repayment_cycle_id' => $request->input('repayment_cycle_id'),
                    'down_payment_rate_id' => $request->input('down_payment_rate_id'),
                ]);

                $creditCheckerVerification->credit_check_no = $this->generateCreditCheckNumber($creditCheckerVerification->id, $creditCheckerVerification->initiated_by);
                $creditCheckerVerification->update();
                if ($request->has('documents')) {
                    $documents = $request->documents;
                    $customerDocuments = [];
                    foreach ($documents as $key => $document) {
                        $customerDocuments[] =  $this->moldDocument($document['name'], $document['url']);
                    }
                    $creditCheckerVerification->documents()->saveMany($customerDocuments);
                }

            }
            $this->sendCreditCheckMailToAdmin($customer, $vendor, $product, $creditCheckerVerification);
            return $this->respondSuccess(['credit_check_verification' =>  $creditCheckerVerification], 'Credit check initiated and notification sent');
        } catch (\Throwable $th) {
            Log::error($th);
            return $this->respondError('An error ocurred while try to initiate credit check');
        }
    }


    public function reInitiateCreditCheck(Request $request)
    {
        $this->validate($request, []);
    }

    public function verifyCreditCheck(CreditCheckerVerification $creditCheckerVerification)
    {
        try {
            /** @var User $vendor */
            $vendor = auth()->user();
            if ($creditCheckerVerification->status === CreditCheckerVerification::PENDING) {
                $this->sendCreditCheckMailToAdmin($creditCheckerVerification->customer, $creditCheckerVerification->vendor, $creditCheckerVerification->product, $creditCheckerVerification);
                return $this->respondSuccess(['status' => CreditCheckerVerification::PENDING], 'Credit check still pending, please check again in 5 minutes');
            }
            if ($creditCheckerVerification->status === CreditCheckerVerification::FAILED) {
                return $this->respondSuccess(['status' => CreditCheckerVerification::FAILED], 'Credit Check failed');
            }
            if ($creditCheckerVerification->status === CreditCheckerVerification::PASSED) {
                return $this->respondSuccess(['status' => CreditCheckerVerification::PASSED], 'Credit check has been passed');
            }
        } catch (\Throwable $th) {
            Log::error($th);
            return $this->respondError('An error ocurred while try to verify credit check status');
        }
    }

    public function sendCreditCheckMailToAdmin($customer, $vendor, $product, $creditCheckerVerification)
    {
        try {
            $isInProduction = App::environment() === 'production';
            $creditCheckerMail =  config('app.credit_checker_mail');
            //check if there is an authenticated user and app is not in production
            //if there is an authenticated user and is not in production
            // the authenticated user phone receives the message
            if (Auth::check() && !$isInProduction) {
                $creditCheckerMail = auth()->user()->email ?  auth()->user()->email : $creditCheckerMail;
            }
            Notification::route('mail', $creditCheckerMail)->notify(new PendingCreditCheckNotification($customer, $vendor, $product, $creditCheckerVerification));
        } catch (\Throwable $th) {
            Log::error($th);
        }
    }

    

    public function moldDocument($documentName, $documentUrl)
    {
        $document = new NewDocument();
        $document->document_url = $documentUrl;

        $document->user_id = auth()->id();
        $document->name = $documentName;
        $document->document_type = Str::slug($documentName, '_');

        return $document;
    }

    public function generateCreditCheckNumber($creditCheckerVerificationId, $vendorId)
    {
        return 'CR/' . $creditCheckerVerificationId . '/' . $vendorId;
    }
}
