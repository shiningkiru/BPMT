<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ForgotPassword extends Mailable
{
    use Queueable, SerializesModels;
    public $email;
    public $hash;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $hash)
    {
        $this->email = $email;
        $this->hash = $hash;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.forgotPassword',[
                        'email'=>$this->email,
                        'hash'=>$this->hash,
                    ]);
    }
}
