<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $username;
    public $link;
    public $content;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($username, $content, $link)
    {
        $this->username=$username;
        $this->content=$content;
        $this->link="https://tool.bixbytessolutions.com".$link;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.notification',[
                'username'=>$this->username,
                'content'=>$this->content,
                'link'=>$this->link,
            ]);
    }
}
