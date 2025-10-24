<?php

namespace App\Listeners;

use App\Events\UserCreatedEvent;
use App\Jobs\SendPasswordOnRegisterJob;
use App\Services\UserService;
use Illuminate\Support\Facades\Log;

class UserCreatedListener
{
    public function __construct() {}

    public function handle(UserCreatedEvent $event): void
    {
        // отправляем письмо с логином/паролем после успешной регистрации
        dispatch(job: new SendPasswordOnRegisterJob(userDTO: $event->user));
    }
}
