<?php

namespace App\Enum;

enum Location: string
{
    case A1 = 'A1'; case A2 = 'A2'; case A3 = 'A3'; case A4 = 'A4'; case A5 = 'A5'; case A6 = 'A6'; case A7 = 'A7'; case A8 = 'A8'; case A9 = 'A9'; case A10 = 'A10';
    case B1 = 'B1'; case B2 = 'B2'; case B3 = 'B3'; case B4 = 'B4'; case B5 = 'B5'; case B6 = 'B6'; case B7 = 'B7'; case B8 = 'B8'; case B9 = 'B9'; case B10 = 'B10';
    case C1 = 'C1'; case C2 = 'C2'; case C3 = 'C3'; case C4 = 'C4'; case C5 = 'C5'; case C6 = 'C6'; case C7 = 'C7'; case C8 = 'C8'; case C9 = 'C9'; case C10 = 'C10';
    case D1 = 'D1'; case D2 = 'D2'; case D3 = 'D3'; case D4 = 'D4'; case D5 = 'D5'; case D6 = 'D6'; case D7 = 'D7'; case D8 = 'D8'; case D9 = 'D9'; case D10 = 'D10';
    case E1 = 'E1'; case E2 = 'E2'; case E3 = 'E3'; case E4 = 'E4'; case E5 = 'E5'; case E6 = 'E6'; case E7 = 'E7'; case E8 = 'E8'; case E9 = 'E9'; case E10 = 'E10';
    case F1 = 'F1'; case F2 = 'F2'; case F3 = 'F3'; case F4 = 'F4'; case F5 = 'F5'; case F6 = 'F6'; case F7 = 'F7'; case F8 = 'F8'; case F9 = 'F9'; case F10 = 'F10';
    case G1 = 'G1'; case G2 = 'G2'; case G3 = 'G3'; case G4 = 'G4'; case G5 = 'G5'; case G6 = 'G6'; case G7 = 'G7'; case G8 = 'G8'; case G9 = 'G9'; case G10 = 'G10';
    case H1 = 'H1'; case H2 = 'H2'; case H3 = 'H3'; case H4 = 'H4'; case H5 = 'H5'; case H6 = 'H6'; case H7 = 'H7'; case H8 = 'H8'; case H9 = 'H9'; case H10 = 'H10';
    case I1 = 'I1'; case I2 = 'I2'; case I3 = 'I3'; case I4 = 'I4'; case I5 = 'I5'; case I6 = 'I6'; case I7 = 'I7'; case I8 = 'I8'; case I9 = 'I9'; case I10 = 'I10';
    case J1 = 'J1'; case J2 = 'J2'; case J3 = 'J3'; case J4 = 'J4'; case J5 = 'J5'; case J6 = 'J6'; case J7 = 'J7'; case J8 = 'J8'; case J9 = 'J9'; case J10 = 'J10';

    case R1 = 'R1';
    case R2 = 'R2';

    public function isRamp(): bool {
        return match ($this) {
            self::R1, self::R2 => true,
            default => false,
        };
    }
}
