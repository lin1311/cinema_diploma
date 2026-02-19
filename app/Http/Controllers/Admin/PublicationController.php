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
        $activePublication = Publication::query()->active()->latest('created_at')->first();
        if ($activePublication) {
            Publication::query()
                ->active()
                ->update([
                    'is_active' => false,
                    'closed_at' => now(),
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Продажи закрыты.',
                'sales_open' => false,
            ]);
        }

        $validationMessage = $this->validateBeforePublication();
        if ($validationMessage !== null) {
            return response()->json([
                'success' => false,
                'message' => $validationMessage,
                'errors' => [
                    'publication' => [$validationMessage],
                ],
            ], 422);
        }

        $payload = $this->buildPayload();

        $publication = DB::transaction(function () use ($payload) {
            Publication::query()->active()->update([
                'is_active' => false,
                'closed_at' => now(),
                'updated_at' => now(),
            ]);

            return Publication::create([
                'payload' => $payload,
                'is_active' => true,
                'opened_at' => now(),
                'closed_at' => null,
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
            'message' => 'Продажи открыты.',
            'published_at' => $publication->created_at?->toISOString(),
            'sales_open' => true,
        ]);
    }

    private function validateBeforePublication(): ?string
    {
        $halls = Hall::orderBy('id')->get(['id', 'name', 'scheme_json']);
        if ($halls->isEmpty()) {
            return 'Добавьте хотя бы один зал перед открытием продаж.';
        }

        if (ChairType::count() === 0) {
            ChairType::firstOrCreate(['code' => 'standart'], ['title' => 'Обычное']);
            ChairType::firstOrCreate(['code' => 'vip'], ['title' => 'VIP']);
        }

        $chairTypes = ChairType::orderBy('id')->get(['id', 'code']);
        $standardType = $chairTypes->firstWhere('code', 'standart')
            ?? $chairTypes->firstWhere('code', 'standard');
        $vipType = $chairTypes->firstWhere('code', 'vip');

        if (!$standardType || !$vipType) {
            return 'Не удалось определить типы кресел для проверки цен.';
        }

        foreach ($halls as $hall) {
            if (!$this->isHallSchemeConfigured($hall->scheme_json)) {
                return "Заполните конфигурацию зала «{$hall->name}».";
            }

            $standardPrice = ChairPrice::query()
                ->where('hall_id', $hall->id)
                ->where('chair_type_id', $standardType->id)
                ->value('price');
            if (!is_numeric($standardPrice) || (int) $standardPrice < 1) {
                return "В зале «{$hall->name}» стоимость обычного места должна быть больше 0.";
            }

            $vipPrice = ChairPrice::query()
                ->where('hall_id', $hall->id)
                ->where('chair_type_id', $vipType->id)
                ->value('price');
            if (!is_numeric($vipPrice) || (int) $vipPrice < 1) {
                return "В зале «{$hall->name}» стоимость VIP-места должна быть больше 0.";
            }
        }

        return null;
    }

    private function isHallSchemeConfigured(mixed $scheme): bool
    {
        if (!is_array($scheme)) {
            return false;
        }

        if (!isset($scheme['rows'], $scheme['seats'], $scheme['seatsGrid'])) {
            return false;
        }

        $rows = (int) $scheme['rows'];
        $seats = (int) $scheme['seats'];
        $grid = $scheme['seatsGrid'];

        if ($rows < 1 || $seats < 1 || !is_array($grid) || count($grid) !== $rows) {
            return false;
        }

        foreach ($grid as $row) {
            if (!is_array($row) || count($row) !== $seats) {
                return false;
            }
        }

        return true;
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
