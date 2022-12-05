<?php

namespace App\Enums;

enum RegionTypeEnum: string
{
    case Provincie = 'provincie';
    case Gemeente = 'gemeente';
    case Wijk = 'wijk';
    case Buurt = 'buurt';
}
