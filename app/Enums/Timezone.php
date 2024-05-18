<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Timezone: string implements HasLabel
{
    case UTC = 'UTC';
    case GMT = 'GMT';
    case EST = 'America/New_York'; // Eastern Standard Time
    case CST = 'America/Chicago';  // Central Standard Time
    case MST = 'America/Denver';   // Mountain Standard Time
    case PST = 'America/Los_Angeles'; // Pacific Standard Time
    case AKST = 'America/Anchorage';  // Alaska Standard Time
    case HST = 'Pacific/Honolulu';    // Hawaii-Aleutian Standard Time
    case CET = 'Europe/Berlin';   // Central European Time
    case EET = 'Europe/Helsinki'; // Eastern European Time
    case WET = 'Europe/Lisbon';   // Western European Time
    case IST = 'Asia/Kolkata';    // India Standard Time
    case JST = 'Asia/Tokyo';      // Japan Standard Time
    case AEST = 'Australia/Sydney'; // Australian Eastern Standard Time
    case ACST = 'Australia/Adelaide'; // Australian Central Standard Time
    case AWST = 'Australia/Perth'; // Australian Western Standard Time
    case NZST = 'Pacific/Auckland'; // New Zealand Standard Time
    case CST_CHINA = 'Asia/Shanghai'; // China Standard Time
    case KST = 'Asia/Seoul';      // Korea Standard Time
    case SGT = 'Asia/Singapore';  // Singapore Time
    case HKT = 'Asia/Hong_Kong';  // Hong Kong Time
    case MSK = 'Europe/Moscow';   // Moscow Standard Time
    case AST = 'Asia/Riyadh';     // Arabia Standard Time
    case ART = 'Africa/Cairo';    // Eastern European Time (Africa)
    case EAT = 'Africa/Nairobi';  // East Africa Time
    case SAST = 'Africa/Johannesburg'; // South Africa Standard Time

    public const DEFAULT = self::UTC;

    public function getLabel(): ?string
    {
        return $this->value;
    }
}
