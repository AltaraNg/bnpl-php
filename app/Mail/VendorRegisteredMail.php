<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Mail\Mailables\Address;

class VendorRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    public User|Builder|array $vendor;
    public string $url;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($url, User|Builder|array $vendor)
    {
        $this->url = $url;
        $this->vendor = $vendor;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            from: new Address(config('app.admin_tech'), 'Altara Credit Ltd'),
            cc: [
                new Address(config('app.admin_tech'), 'Altara Credit Ltd'),
            ],
            subject: 'Welcome to Altara',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.vendor-registered',
            with: [
                'url' => $this->url,
                'name' => $this->vendor->full_name,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
