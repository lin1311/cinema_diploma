<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\BlocksChangesWhenSalesOpen;
use App\Models\Movie;
use App\Models\Seance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SeanceController extends Controller
{
    use BlocksChangesWhenSalesOpen;

    public function store(Request $request)
    {
        if ($response = $this->denyIfSalesOpen($request)) {
            return $response;
        }

        $validator = Validator::make(
            $request->all(),
            [
                'seances' => ['required', 'array'],
                'seances.*.hall_id' => ['required', 'integer', 'exists:halls,id'],
                'seances.*.movie_id' => ['required', 'integer', 'exists:movies,id'],
                'seances.*.start_time' => ['required', 'date_format:H:i'],
            ],
            [
                'seances.required' => 'Список сеансов не передан.',
                'seances.array' => 'Список сеансов должен быть массивом.',
                'seances.*.hall_id.required' => 'Для каждого сеанса должен быть выбран зал.',
                'seances.*.hall_id.exists' => 'Указан несуществующий зал.',
                'seances.*.movie_id.required' => 'Для каждого сеанса должен быть выбран фильм.',
                'seances.*.movie_id.exists' => 'Указан несуществующий фильм.',
                'seances.*.start_time.required' => 'Для каждого сеанса должно быть указано время начала.',
                'seances.*.start_time.date_format' => 'Время начала должно быть в формате ЧЧ:ММ.',
            ]
        );

        $validator->after(function ($validator) use ($request) {
            $seances = $request->input('seances', []);
            if (!is_array($seances)) {
                return;
            }

            $duplicates = [];
            foreach ($seances as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $key = ($item['hall_id'] ?? '') . '|' . ($item['start_time'] ?? '');
                if (isset($duplicates[$key])) {
                    $validator->errors()->add('seances', 'В одном зале не может быть двух сеансов на одно и то же время.');
                    break;
                }
                $duplicates[$key] = true;
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $movieIds = collect($seances)
                ->pluck('movie_id')
                ->filter()
                ->unique()
                ->values();

            $durations = Movie::query()
                ->whereIn('id', $movieIds)
                ->pluck('duration_minutes', 'id');

            $groupedByHall = [];
            foreach ($seances as $index => $item) {
                if (!is_array($item)) {
                    continue;
                }

                $hallId = (int) ($item['hall_id'] ?? 0);
                $movieId = (int) ($item['movie_id'] ?? 0);
                $startTime = (string) ($item['start_time'] ?? '');
                $duration = (int) ($durations[$movieId] ?? 0);

                if ($duration <= 0) {
                    $validator->errors()->add(
                        "seances.$index.movie_id",
                        'Для выбранного фильма не указана корректная длительность.'
                    );
                    continue;
                }

                $startMinutes = $this->timeToMinutes($startTime);
                if ($startMinutes === null) {
                    continue;
                }

                $groupedByHall[$hallId][] = [
                    'index' => $index,
                    'start_time' => $startTime,
                    'start_minutes' => $startMinutes,
                    'end_minutes' => $startMinutes + $duration,
                ];
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            foreach ($groupedByHall as $hallSeances) {
                usort($hallSeances, function ($left, $right) {
                    return $left['start_minutes'] <=> $right['start_minutes'];
                });

                $count = count($hallSeances);
                for ($i = 1; $i < $count; $i++) {
                    $prev = $hallSeances[$i - 1];
                    $current = $hallSeances[$i];

                    if ($current['start_minutes'] < $prev['end_minutes']) {
                        $validator->errors()->add(
                            'seances',
                            sprintf(
                                'Сеанс в %s пересекается с сеансом в %s в этом же зале. Измените время.',
                                $current['start_time'],
                                $prev['start_time']
                            )
                        );
                        return;
                    }
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

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

    private function timeToMinutes(string $time): ?int
    {
        try {
            $parsed = Carbon::createFromFormat('H:i', $time);
        } catch (\Throwable) {
            return null;
        }

        return ((int) $parsed->format('H') * 60) + (int) $parsed->format('i');
    }
}
