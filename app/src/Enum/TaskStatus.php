<?php

namespace App\Enum;

enum TaskStatus: string
{
    case ASSIGNED = 'assigned';
    case COMPLETED = 'completed';
    case PENDING = 'pending';
}
