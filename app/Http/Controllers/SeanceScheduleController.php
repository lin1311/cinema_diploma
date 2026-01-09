<?php

namespace App\Http\Controllers;

use App\Models\Publication;

class SeanceScheduleController extends Controller{
    public function index()
    {
        $publication = Publication::latest('created_at')->first();

        if (!$publication) {
            return response()->json([
                'halls' => [],
                'movies' => [],
                'seances' => [],
                'prices' => [],
            ]);
        }

        $payload = $publication->payload ?? [];

        return response()->json([
            'halls' => $payload['halls'] ?? [],
            'movies' => $payload['movies'] ?? [],
            'seances' => $payload['seances'] ?? [],
            'prices' => $payload['prices'] ?? [],
            'published_at' => $payload['published_at'] ?? null,
        ]);
    }
}
