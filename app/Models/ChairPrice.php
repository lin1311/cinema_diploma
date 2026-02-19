<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChairPrice extends Model
{
    protected $table = 'chair_prices';
    protected $fillable = ['hall_id', 'chair_type_id', 'price'];

    // Связь с залом
    public function hall()
    {
        return $this->belongsTo(Hall::class, 'hall_id');
    }

    // Связь с типом кресла
    public function chairType()
    {
        return $this->belongsTo(ChairType::class, 'chair_type_id');
    }
}
