<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChairType extends Model
{
    protected $table = 'chair_types';
    protected $fillable = ['code', 'title'];

    public function prices()
    {
        return $this->hasMany(ChairPrice::class, 'chair_type_id');
    }
}