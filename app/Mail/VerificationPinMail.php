<?php

namespace App\Mail;
use Illuminate\Mail\Mailable;


class VerificationPinMail extends Mailable
{
    public $pin;

    public function __construct($pin)
    {
        $this->pin = $pin;
    }

    public function build()
    {
        return $this->subject('Tu código de verificación')
            ->view('emails.verification-pin');
    }
}


