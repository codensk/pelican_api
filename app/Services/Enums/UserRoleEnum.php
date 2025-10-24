<?php

namespace App\Services\Enums;

enum UserRoleEnum: string
{
    case user = 'user';
    case client = 'client';
    case admin = 'admin';
}
