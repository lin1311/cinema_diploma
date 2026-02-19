<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hall extends Model
{
    protected $table = 'halls';
    protected $fillable = ['name', 'scheme_json'];

    protected $casts = [
        'scheme_json' => 'array'
    ];

    public function chairPrices()
    {
       return $this->hasMany(ChairPrice::class, 'hall_id');
    }

    public function getPricesByType()
    {
        return $this->chairPrices()->with('chairType')->get()
            ->mapWithKeys(fn($cp) => [$cp->chairType->code => $cp->price])
            ->toArray();
    }
}
