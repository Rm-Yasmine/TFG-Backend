<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;


class VerifyEmailCode extends Mailable
{
 public $code;

public function __construct($code)
{
    $this->code = $code;
}

public function build()
{
    return $this->subject('Tu código de verificación')
        ->view('emails.verify-code');
}

}
