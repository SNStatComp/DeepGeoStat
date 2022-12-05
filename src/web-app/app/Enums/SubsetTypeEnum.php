<?php

namespace App\Enums;

enum SubsetTypeEnum: int
{
    case RandomSample = 0;
    case File = 1;
    case Regions = 2;
}
