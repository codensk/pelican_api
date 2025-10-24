<?php

namespace App\Services;

use App\Mail\PasswordOnRegister;
use Illuminate\Support\Facades\Mail;

class MailService
{
    public static function sendPasswordOnRegister(string $to, string $subject, string $email, string $password): void {
        Mail::to($to)
            ->send(new PasswordOnRegister(messageSubject: $subject, email: $email, password: $password));
    }
}
