<?php

namespace App\Services;

use App\Mail\PasswordOnRegister;
use App\Mail\PaymentLinkOnOrderCreated;
use Illuminate\Support\Facades\Mail;

class MailService
{
    public static function sendPasswordOnRegister(string $to, string $subject, string $email, string $password): void {
        Mail::to($to)
            ->send(new PasswordOnRegister(messageSubject: $subject, email: $email, password: $password));
    }

    public static function sendPaymentLinkOnOrderCreated(string $to, string $subject, string $orderId, string $link, string $expiresAt): void {
        Mail::to($to)
            ->send(new PaymentLinkOnOrderCreated(messageSubject: $subject, orderId: $orderId, link: $link, expiresAt: $expiresAt));
    }
}
