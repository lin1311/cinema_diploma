<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeanceController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'seances' => 'nullable|array',
            'seances.*.hall_id' => 'required|integer|exists:halls,id',
            'seances.*.movie_id' => 'required|integer|exists:movies,id',
            'seances.*.start_time' => 'required|date_format:H:i',
        ]);

        $seances = $validated['seances'] ?? [];

        DB::transaction(function () use ($seances) {
            Seance::query()->delete();

            if (!empty($seances)) {
                Seance::insert(array_map(function ($seance) {
                    return [
                        'hall_id' => $seance['hall_id'],
                        'movie_id' => $seance['movie_id'],
                        'start_time' => $seance['start_time'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }, $seances));
            }
        });

        return response()->json(['success' => true]);
    }
}

