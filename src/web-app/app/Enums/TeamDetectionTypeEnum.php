<?php

namespace App\Enums;

enum TeamDetectionTypeEnum: int
{
    case Classification = 1;
    case ChangeDetection = 2;
}
