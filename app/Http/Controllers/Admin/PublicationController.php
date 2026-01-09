<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChairPrice;
use App\Models\ChairType;
use App\Models\Hall;
use App\Models\Movie;
use App\Models\Publication;
use App\Models\Seance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PublicationController extends Controller
{
    public function store(Request $request)
    {
        $payload = $this->buildPayload();

        $publication = DB::transaction(function () use ($payload) {
            return Publication::create([
                'payload' => $payload,
            ]);
        });

        if (!$publication) {
            return response()->json([
                'success' => false,
                'message' => 'Не удалось опубликовать данные.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Данные успешно опубликованы.',
            'published_at' => $publication->created_at?->toISOString(),
        ]);
    }

    private function buildPayload(): array
    {
        $halls = Hall::orderBy('id')->get(['id', 'name', 'scheme_json']);

        if (ChairType::count() === 0) {
            ChairType::firstOrCreate(['code' => 'standart'], ['title' => 'Обычное']);
            ChairType::firstOrCreate(['code' => 'vip'], ['title' => 'VIP']);
        }

        $chairTypes = ChairType::orderBy('id')->get(['id', 'code']);

        $prices = [];
        foreach ($halls as $hall) {
            foreach ($chairTypes as $type) {
                $price = ChairPrice::where('hall_id', $hall->id)
                    ->where('chair_type_id', $type->id)
                    ->value('price');

                $code = $type->code === 'standard' ? 'standart' : $type->code;
                $prices[$hall->id][$code] = $price !== null ? (int) $price : null;
            }
        }

        $hallsPayload = $halls->map(function (Hall $hall) {
            $scheme = $hall->scheme_json;

            if (is_array($scheme)) {
                $normalized = $scheme;
            } elseif (is_object($scheme)) {
                $normalized = (array) $scheme;
            } else {
                $normalized = [];
            }

            return [
                'id' => $hall->id,
                'name' => $hall->name,
                'scheme' => $normalized,
            ];
        })->values();

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
            })
            ->values();

        return [
            'halls' => $hallsPayload,
            'prices' => $prices,
            'movies' => $movies,
            'seances' => $seances,
            'published_at' => Carbon::now()->toISOString(),
        ];
    }
}

