<?php

namespace App\Enums;

enum E_Status: string {
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case PENDING = 'pending';
}
