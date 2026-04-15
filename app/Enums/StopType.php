<?php

namespace App\Enums;

enum StopType: string
{
    case Scenic = 'scenic';
    case Ecological = 'ecological';
    case Historical = 'historical';
    case Artistic = 'artistic';

    public function label(): string
    {
        return match($this) {
            self::Scenic => 'Scenic',
            self::Ecological => 'Ecological',
            self::Historical => 'Historical',
            self::Artistic => 'Artistic',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Scenic => 'info',
            self::Ecological => 'success',
            self::Historical => 'warning',
            self::Artistic => 'danger',
        };
    }
}
