<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FactureMail extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * The data.
     *
     * @var array
     */
    public $filename;

    /**
     * Create a new message instance.
     *@var filename
     * @return void
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.facture');
    }
}
