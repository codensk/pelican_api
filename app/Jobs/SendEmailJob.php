<?php

namespace App\Jobs;

use App\Mail\NewMail;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public string $to;
    public string $subject;
    public string $message;
    public array $files;

    public function __construct(string $to, string $subject, string $message, array $files = [])
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->message = $message;
        $this->files = $files;
    }

    public function handle(): void
    {
        if (! defined('CURL_SSLVERSION_TLSv1_2')) {
            define('CURL_SSLVERSION_TLSv1_2', 6); // Значение 6 соответствует TLS 1.2
        }

        Mail::to($this->to)->send(new NewMail($this->message, $this->subject, env("MAIL_FROM_ADDRESS"), $this->files));
    }
}
