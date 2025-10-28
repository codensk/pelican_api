<?php

namespace App\Jobs;

use App\DTO\UserDTO;
use App\Services\UserService;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPasswordOnRegisterJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public UserDTO $userDTO;
    public UserService $userService;

    public function __construct(UserDTO $userDTO)
    {
        $this->userDTO = $userDTO;
        $this->userService = app(UserService::class);
    }

    public function handle(): void
    {
        if (! defined('CURL_SSLVERSION_TLSv1_2')) {
            define('CURL_SSLVERSION_TLSv1_2', 6); // Значение 6 соответствует TLS 1.2
        }

        try {
            $this->userService->sendPasswordOnRegister(userDTO: $this->userDTO);
        } catch (Exception $exception) {
            $this->release(10);
        }
    }
}
