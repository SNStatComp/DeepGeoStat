<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mask extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'polygon', 'geometry', 'options',
    ];

    protected $casts = [
        'polygon' => 'array',
        'options' => 'array',
    ];

    public function getCssMask()
    {
        return collect($this->polygon)->map(function ($coordinates) {
            return collect($coordinates)->map(function ($coordinate) {
                return collect($coordinate)->map(function ($point) {
                    return (($point / 500) * 100).'%';
                })->implode(' ');
            })->implode(', ');
        });
    }
}
