<?php

namespace App\Enums;

enum InspectSampleMethodEnum: int
{
    case RandomSample = 0;
    case EqualClassSizeSample = 1;
    // case StratifiedSample = 2;
}
