<?php

namespace App\Listeners;

use App\Events\VendorRegisteredEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
//        dd($event->vendor, $event->otp);
    }
}
