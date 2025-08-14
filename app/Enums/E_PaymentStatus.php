<?php

namespace App\Enums;

enum E_PaymentStatus: string
{
    case PENDING = 'pending';
    case SUCCESSFUL = 'successful';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
    case PAID = 'paid';
    case PARTIALLY_PAID = 'partially_paid';
    case CANCELLED = 'cancelled';
}
