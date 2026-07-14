<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BfrnUserCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $details) {}

    public function build()
    {
        return $this->subject('BFRN Access Created')
            ->view('emails.auth-gateway.bfrn-user-created')
            ->with(['details' => $this->details]);
    }
}
