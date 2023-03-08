<?php

namespace App\Listeners;

use App\Events\VendorRegisteredEvent;
use App\Exceptions\SmsMessageFailedToSendException;
use App\Mail\VendorRegisteredMail;
use App\Services\SendSmsService;
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
    public function __construct(public readonly SendSmsService $sendSmsService)
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
                $this->sendSmsService->sendMessage($vendor->phone_number, $message);
            }
        } catch (\Throwable $th) {
            Log::error($th);
        }

        try {
            if ($vendor->email && env('APP_SEND_EMAIL')) {
                Mail::to($vendor)->send(new VendorRegisteredMail($url, $vendor));
            }
        } catch (\Throwable $th) {
            Log::error($th);
        }
    }
}
