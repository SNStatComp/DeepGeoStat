<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurfaceUsageFilter extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'title', 'code', 'year',
    ];

    public function grids()
    {
        return $this->hasMany(SurfaceUsageFilterGrid::class);
    }

    public function registers()
    {
        return $this->registersSource->merge($this->registersChange);
    }

    public function registersSource()
    {
        return $this->morphMany(Register::class, 'source');
    }

    public function registersChange()
    {
        return $this->morphMany(Register::class, 'change_source');
    }
}
