<?php

namespace App\Listeners;

use App\Events\VendorRegisteredEvent;
use App\Mail\VendorRegisteredMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Mail;

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
        Http::withHeaders([
            'BNLP-ADMIN-ACCESS' => env('BNLP_ADMIN_ACCESS'),
        ])->post(env('ALTARA_PORTAL_BASE_URL') . '/bnlp/send/message', [
            'phone_number' => $vendor->phone_number,
            'message' => $message,
        ]);
        Mail::to($request->user())->send(new VendorRegisteredMail($url, $vendor));
    }
}
