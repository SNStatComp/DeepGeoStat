<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurfaceUsageFilterGrid extends Model
{
    use HasFactory;

    public function mask()
    {
        return $this->belongsTo(Mask::class);
    }
}
