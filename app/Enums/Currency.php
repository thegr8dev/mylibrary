<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Currency: string implements HasLabel
{
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case JPY = 'JPY';
    case AUD = 'AUD';
    case CAD = 'CAD';
    case CHF = 'CHF';
    case CNY = 'CNY';
    case HKD = 'HKD';
    case NZD = 'NZD';
    case SEK = 'SEK';
    case KRW = 'KRW';
    case SGD = 'SGD';
    case NOK = 'NOK';
    case MXN = 'MXN';
    case INR = 'INR';
    case RUB = 'RUB';
    case ZAR = 'ZAR';
    case TRY = 'TRY';
    case BRL = 'BRL';
    case TWD = 'TWD';
    case DKK = 'DKK';
    case PLN = 'PLN';
    case THB = 'THB';
    case IDR = 'IDR';
    case HUF = 'HUF';
    case CZK = 'CZK';
    case ILS = 'ILS';
    case CLP = 'CLP';
    case PHP = 'PHP';
    case AED = 'AED';
    case COP = 'COP';
    case SAR = 'SAR';
    case MYR = 'MYR';
    case RON = 'RON';

    public const DEFAULT = self::USD;

    public function getLabel(): ?string
    {
        return $this->value;
    }
}
