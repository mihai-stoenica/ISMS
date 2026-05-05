<?php

namespace App\Enum;

enum ContractStatus: string
{
    case PENDING = 'pending';
    case DONE = 'done';
    case REJECTED = 'rejected';
}
