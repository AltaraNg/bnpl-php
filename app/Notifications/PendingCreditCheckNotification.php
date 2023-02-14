<?php

namespace App\Notifications;

use App\Models\BnplVendorProduct;
use App\Models\CreditCheckerVerification;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PendingCreditCheckNotification extends Notification
{
    use Queueable;

    public Customer $customer;
    public User $vendor;
    public BnplVendorProduct $product;
    public CreditCheckerVerification $creditCheckVerification;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Customer $customer, User $vendor, BnplVendorProduct $product, CreditCheckerVerification $creditCheckerVerification)
    {
        $this->customer = $customer;
        $this->vendor = $vendor;
        $this->product = $product;
        $this->creditCheckVerification = $creditCheckerVerification;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)->view('pending-credit-check', [
            'customer_name' => $this->customer->first_name . ' ' . $this->customer->last_name,
            'vendor_name' => $this->vendor->full_name,
            'product_name' => $this->product->product_name,
            'vendor_phone_number' => $this->vendor->phone_number,
            'customer_phone_number' => $this->customer->telephone,
            'product_price' => $this->product->product_price,
            'url' => url('pending/credit/check/{id}', ['id' => $this->creditCheckVerification->id])
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
