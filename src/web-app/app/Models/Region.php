<?php

namespace App\Models;

use App\Enums\RegionTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $casts = [
        'region_type' => RegionTypeEnum::class,
    ];
}
