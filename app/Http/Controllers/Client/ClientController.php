<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\Movie;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        $publication = Publication::latest('created_at')->first();
        $payload = $publication?->payload ?? [];

        $halls = collect($payload['halls'] ?? []);
        $seances = collect($payload['seances'] ?? []);

        $seancesByMovie = $seances
            ->groupBy('movie_id')
            ->map(function ($movieSeances) {
                return $movieSeances
                    ->groupBy('hall_id')
                    ->map(function ($hallSeances) {
                        return $hallSeances->sortBy('start_time')->values();
                    });
            });

        $movieIdsWithSeances = $seancesByMovie->keys()->all();
        $moviesToShow = Movie::whereIn('id', $movieIdsWithSeances)
            ->orderBy('id')
            ->get();

        return view('client.index', [
            'movies' => $moviesToShow,
            'hallsById' => $halls->keyBy('id'),
            'seancesByMovie' => $seancesByMovie,
        ]);
    }
}
