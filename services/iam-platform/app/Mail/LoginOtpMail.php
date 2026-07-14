<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoginOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $details)
    {
    }

    public function build()
    {
        return $this->subject('Your IAM Platform Login Code')
            ->view('emails.auth-gateway.login-otp')
            ->with(['details' => $this->details]);
    }
}
