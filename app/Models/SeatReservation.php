<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeatReservation extends Model
{
    protected $table = 'seat_reservations';

    protected $fillable = [
        'seance_id',
        'seance_date',
        'row',
        'seat',
        'status',
    ];
}
