<?php

namespace App\Services;

use App\DTO\OrderDTO;
use App\Mail\BookingMessage;
use App\Mail\NotificationOnSuccessPayment;
use App\Mail\PasswordOnRegister;
use App\Mail\PaymentLinkOnOrderCreated;
use App\Mail\SendVoucher;
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

    public static function sendNotificationOnSuccessPayment(string $to, string $subject, string $orderId): void {
        Mail::to($to)
            ->send(new NotificationOnSuccessPayment(messageSubject: $subject, orderId: $orderId));
    }

    public static function sendBookingMessage(string $to, string $subject, string $messageText, OrderDTO $orderDTO): void {
        Mail::to($to)
            ->send(new BookingMessage(messageSubject: $subject, messageText: $messageText, orderDTO: $orderDTO));
    }

    public static function sendVoucher(string $to, string $subject, string $orderId, string $voucher): void {
        Mail::to($to)
            ->send(new SendVoucher(messageSubject: $subject, orderId: $orderId, voucher: $voucher));
    }
}
