<?php

namespace App\Services\Enums;

enum TicketTypeEnum: string
{
    case Refundable = 'refundable';
    case NonRefundable = 'non_refundable';
}
