<?php

namespace App\Listeners;

use App\Events\VendorRegisteredEvent;
use App\Exceptions\SmsMessageFailedToSendException;
use App\Mail\VendorRegisteredMail;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use function PHPUnit\Framework\throwException;

class SendVendorWelcomeMessageListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param VendorRegisteredEvent $event
     * @return void
     */
    public function handle(VendorRegisteredEvent $event)
    {
        $vendor = $event->vendor;
        $otp = $event->otp;
        $url = $event->url;
        $message = "Welcome to Altara! We're glad to have you as a merchant. Please follow this link " . $url . " to change your password. Let us know if you have any issues.";
        //send message
        try {
            if ($vendor->phone_number) {
                $response =  Http::withHeaders([
                    'BNLP-ADMIN-ACCESS' => env('BNLP_ADMIN_ACCESS'),
                ])->post(env('ALTARA_PORTAL_BASE_URL') . '/bnlp/send/message', [
                    'phone_number' => $this->appendPrefix($vendor->phone_number),
                    'message' => $message,
                ]);
                $statusName = $response->json()['data']['response']['messages'][0]['status']['groupName'];
                $statusDescription = $response->json()['data']['response']['messages'][0]['status']['description'];
                if ($statusName !== 'Success') {
                    throw new SmsMessageFailedToSendException($statusDescription);
                }
            }
            if ($vendor->email && env('APP_SEND_EMAIL')) {
                Mail::to($vendor)->send(new VendorRegisteredMail($url, $vendor));
            }
        } catch (\Throwable $th) {
            Log::error($th);
            throw new Exception($th->getMessage());
        }
    }

    private function appendPrefix(string $number)
    {
        if (!$number) return '';
        $pre = '234';
        if ($number[0] == 0) {
            return $pre . substr($number, 1);
        } elseif (substr($number, 0, 3) == $pre) {
            return $number;
        }
        return $pre . $number;
    }
}
