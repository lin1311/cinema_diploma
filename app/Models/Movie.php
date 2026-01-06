<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $table = 'movies';

    protected $appends = [
        'duration',
    ];


    protected $fillable = [
        'title',
        'duration',
        'duration_minutes',
        'description',
        'country',
        'poster',
        'poster_url',
    ];

    public function getDurationAttribute(): ?int
    {
        return $this->attributes['duration_minutes'] ?? null;
    }

    public function setDurationAttribute($value): void
    {
        $this->attributes['duration_minutes'] = $value;
    }
}
