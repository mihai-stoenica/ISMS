<?php

namespace App\Enum;

enum Location: string
{
    case A1 = 'A1';
    case A2 = 'A2';
    case A3 = 'A3';
    case A4 = 'A4';
    case A5 = 'A5';
    case A6 = 'A6';
    case A7 = 'A7';
    case A8 = 'A8';
    case A9 = 'A9';
    case A10 = 'A10';
    case B1 = 'B1';
    case B2 = 'B2';
    case B3 = 'B3';
    case B4 = 'B4';
    case B5 = 'B5';
    case B6 = 'B6';
    case B7 = 'B7';
    case B8 = 'B8';
    case B9 = 'B9';
    case B10 = 'B10';

    case C1 = 'C1';
    case C2 = 'C2';
    case C3 = 'C3';
    case C4 = 'C4';
    case C5 = 'C5';
    case C6 = 'C6';
    case C7 = 'C7';
    case C8 = 'C8';
    case C9 = 'C9';
    case C10 = 'C10';

    case R1 = 'R1';
    case R2 = 'R2';

    public function isRamp() : bool {
        return match ($this) {
            self::R1, self::R2 => true,
            default => false,
        };
    }
}
