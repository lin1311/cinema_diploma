<?php

namespace App\Http\Controllers;

use App\Models\Hall;
use App\Models\Movie;
use App\Models\Seance;
use Illuminate\Support\Carbon;

class SeanceScheduleController extends Controller
{
    public function index()
    {
        $halls = Hall::orderBy('id')->get(['id', 'name']);
        $movies = Movie::orderBy('id')->get(['id', 'title', 'duration']);
        $seances = Seance::orderBy('start_time')
            ->get(['id', 'hall_id', 'movie_id', 'start_time'])
            ->map(function (Seance $seance) {
                return [
                    'id' => $seance->id,
                    'hall_id' => $seance->hall_id,
                    'movie_id' => $seance->movie_id,
                    'start_time' => Carbon::parse($seance->start_time)->format('H:i'),
                ];
            });

        return response()->json([
            'halls' => $halls,
            'movies' => $movies,
            'seances' => $seances,
        ]);
    }
}
