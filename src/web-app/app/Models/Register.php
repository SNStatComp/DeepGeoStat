<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Register extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'title', 'description',
    ];

    public function source()
    {
        return $this->morphTo();
    }

    public function changeSource()
    {
        return $this->morphTo();
    }

    public function labelClasses()
    {
        return $this->hasMany(RegisterLabelClass::class);
    }

    public function labels()
    {
        return $this->hasMany(RegisterLabel::class);
    }
}
