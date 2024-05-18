<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DateFormat: string implements HasLabel
{
    case FULL_DATE = 'Y-m-d';
    case MONTH_DAY_YEAR = 'm/d/Y';
    case DAY_MONTH_YEAR = 'd/m/Y';
    case DAY_MONTH_YEAR_DASH = 'd-m-Y';
    case DAY_MONTH_SUFFIX_YEAR = 'dS F Y'; // Format like "21st February 2024"
    case DAY_MONTH_ABBR_YEAR = 'd M Y';
    case DAY_MONTH_NAME_YEAR = 'd F Y';
    case MONTH_NAME_DAY_YEAR = 'F d, Y';
    case MONTH_ABBR_DAY_YEAR = 'M d, Y';

    public const DEFAULT = self::DAY_MONTH_YEAR_DASH;

    public function getLabel(): ?string
    {
        return $this->value;
    }
}
