<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CheckConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $person;
    public $tipo;
    public $hora;

    public function __construct($person, $tipo, $hora)
    {
        $this->person = $person;
        $this->tipo = $tipo;
        $this->hora = $hora;
    }

    public function build()
    {
        return $this->subject('Confirmación de ' . $this->tipo)
            ->view('emails.check_confirmation');
    }
}

