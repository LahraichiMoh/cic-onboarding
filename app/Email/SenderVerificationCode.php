<?php

namespace App\Email;

use App\Email\Mailable;
use App\Models\Model;


class SenderVerificationCode extends Mailable
{
    protected $data;

    public function __construct(Array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject("Code de vérification ")
            ->view('emails/verification-code.twig')
            ->with([
                'name' => $data['name'],
                'code' => $data['code']
            ]);
    }
}