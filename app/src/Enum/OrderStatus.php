<?php

namespace App\Enum;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case DONE = 'done';
}
