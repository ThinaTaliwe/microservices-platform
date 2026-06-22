<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupervisorLoginApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $details
    ) {}

    public function build()
    {
        return $this->subject('High Risk Login Approval Required')
            ->view('emails.auth-gateway.supervisor-login-approval')
            ->with(['details' => $this->details]);
    }
}
