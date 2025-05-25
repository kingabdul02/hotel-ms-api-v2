<?php

namespace App\Enums;

enum E_PaymentMethod: string
{
    case ONLINE = 'online';
    case POS = 'pos';
    case TRANSFER = 'transfer';
    case CASH = 'cash';
}
