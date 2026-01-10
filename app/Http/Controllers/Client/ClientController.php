<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\SeatReservation;
use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

        return view('public.index', [
            'movies' => $moviesToShow,
            'hallsById' => $halls->keyBy('id'),
            'seancesByMovie' => $seancesByMovie,
        ]);
    }

    public function hall(Request $request, int $seance): View
    {
        $publication = Publication::latest('created_at')->first();

        if (!$publication) {
            abort(404);
        }

        $payload = $publication->payload ?? [];
        $seances = collect($payload['seances'] ?? []);
        $seanceData = $seances->first(function ($item) use ($seance) {
            return (int) ($item['id'] ?? 0) === $seance;
        });

        if (!$seanceData) {
            abort(404);
        }

        $movies = collect($payload['movies'] ?? [])->map(fn($movie) => (array) $movie);
        $movie = $movies->first(function ($item) use ($seanceData) {
            return (int) ($item['id'] ?? 0) === (int) ($seanceData['movie_id'] ?? 0);
        });

        $halls = collect($payload['halls'] ?? [])->map(fn($hall) => (array) $hall);
        $hall = $halls->first(function ($item) use ($seanceData) {
            return (int) ($item['id'] ?? 0) === (int) ($seanceData['hall_id'] ?? 0);
        });

        $prices = $payload['prices'][$seanceData['hall_id'] ?? null] ?? [];
        $selectedDate = $request->query('date');
        $date = $selectedDate ? Carbon::parse($selectedDate) : Carbon::today();

        $seatMap = SeatReservation::where('seance_id', $seance)
            ->where('status', 'taken')
            ->get()
            ->groupBy('status');

        $takenSeats = $seatMap->get('taken', collect())
            ->mapWithKeys(fn(SeatReservation $seat) => ["{$seat->row}-{$seat->seat}" => true])
            ->all();

        $sessionKey = $this->reservationSessionKey($seance);
        $request->session()->forget($sessionKey);
        $pendingSeats = $request->session()->get($sessionKey, []);
        $selectedSeats = [];

        foreach ($pendingSeats as $seatKey => $seatData) {
            if (empty($takenSeats[$seatKey])) {
                $selectedSeats[$seatKey] = true;
            }
        }

        if (count($selectedSeats) !== count($pendingSeats)) {
            $cleanedSeats = [];
            foreach ($selectedSeats as $seatKey => $selected) {
                $cleanedSeats[$seatKey] = $pendingSeats[$seatKey];
            }
            $request->session()->put($sessionKey, $cleanedSeats);
        }

        return view('public.hall', [
            'movie' => $movie ?? [],
            'hall' => $hall ?? [],
            'seance' => $seanceData,
            'scheme' => $this->normalizeScheme($hall['scheme'] ?? []),
            'prices' => $prices,
            'dateLabel' => $date->format('d.m.Y'),
            'takenSeats' => $takenSeats,
            'selectedSeats' => $selectedSeats,
        ]);
    }

    public function reserveSeats(Request $request, int $seance)
    {
        $validated = $request->validate([
            'row' => ['nullable', 'integer', 'min:1'],
            'seat' => ['nullable', 'integer', 'min:1'],
            'action' => ['required', 'string', 'in:toggle,reserve'],
        ]);

        $publication = Publication::latest('created_at')->first();
        if (!$publication) {
            return response()->json(['message' => 'Нет данных.'], 404);
        }

        $payload = $publication->payload ?? [];
        $seances = collect($payload['seances'] ?? []);
        $seanceData = $seances->first(function ($item) use ($seance) {
            return (int) ($item['id'] ?? 0) === $seance;
        });

        if (!$seanceData) {
            return response()->json(['message' => 'Сеанс не найден.'], 404);
        }

        $halls = collect($payload['halls'] ?? [])->map(fn($hall) => (array) $hall);
        $hall = $halls->first(function ($item) use ($seanceData) {
            return (int) ($item['id'] ?? 0) === (int) ($seanceData['hall_id'] ?? 0);
        });

        if ($validated['action'] === 'reserve') {
            $sessionKey = $this->reservationSessionKey($seance);
            $pendingSeats = $request->session()->get($sessionKey, []);

            if (empty($pendingSeats)) {
                return response()->json(['message' => 'Не выбраны места.'], 422);
            }

            $takenQuery = SeatReservation::where('seance_id', $seance)
                ->where('status', 'taken')
                ->where(function ($query) use ($pendingSeats) {
                    foreach ($pendingSeats as $seatData) {
                        $query->orWhere(function ($seatQuery) use ($seatData) {
                            $seatQuery->where('row', $seatData['row'])
                                ->where('seat', $seatData['seat']);
                        });
                    }
                });

            $takenSeats = $takenQuery->get()
                ->mapWithKeys(fn(SeatReservation $seat) => ["{$seat->row}-{$seat->seat}" => true])
                ->all();

            if (!empty($takenSeats)) {
                $cleanedSeats = [];
                foreach ($pendingSeats as $seatKey => $seatData) {
                    if (empty($takenSeats[$seatKey])) {
                        $cleanedSeats[$seatKey] = $seatData;
                    }
                }
                $request->session()->put($sessionKey, $cleanedSeats);

                return response()->json(['message' => 'Некоторые места уже заняты.'], 409);
            }

            foreach ($pendingSeats as $seatKey => $seatData) {
                SeatReservation::updateOrCreate(
                    [
                        'seance_id' => $seance,
                        'row' => $seatData['row'],
                        'seat' => $seatData['seat'],
                    ],
                    ['status' => 'taken']
                );
            }

            $request->session()->put($sessionKey, $pendingSeats);

            return response()->json([
                'reserved' => count($pendingSeats),
                'redirect' => route('client.hall', ['seance' => $seance]),
            ]);
        }

        if (!$validated['row'] || !$validated['seat']) {
            return response()->json(['message' => 'Место не указано.'], 422);
        }

        $scheme = $this->normalizeScheme($hall['scheme'] ?? []);
        $rowIndex = $validated['row'] - 1;
        $seatIndex = $validated['seat'] - 1;

        $seatType = $scheme['seatsGrid'][$rowIndex][$seatIndex] ?? null;
        if (!$seatType || $seatType === 'disabled') {
            return response()->json(['message' => 'Место недоступно.'], 422);
        }

        $existing = SeatReservation::where('seance_id', $seance)
            ->where('row', $validated['row'])
            ->where('seat', $validated['seat'])
            ->first();

        if ($existing && $existing->status === 'taken') {
            return response()->json(['message' => 'Место уже занято.'], 409);
        }

        $sessionKey = $this->reservationSessionKey($seance);
        $pendingSeats = $request->session()->get($sessionKey, []);
        $seatKey = "{$validated['row']}-{$validated['seat']}";

        if (isset($pendingSeats[$seatKey])) {
            unset($pendingSeats[$seatKey]);
            $selected = false;
        } else {
            $pendingSeats[$seatKey] = [
                'row' => $validated['row'],
                'seat' => $validated['seat'],
            ];
            $selected = true;
        }

        $request->session()->put($sessionKey, $pendingSeats);

        return response()->json([
            'selectedCount' => count($pendingSeats),
            'selected' => $selected,
        ]);
    }

    private function reservationSessionKey(int $seance): string
    {
        return "reservations.pending.{$seance}";
    }

    private function normalizeScheme(mixed $rawScheme): array
    {
        if (is_object($rawScheme)) {
            $rawScheme = (array) $rawScheme;
        }

        if (is_array($rawScheme) && array_is_list($rawScheme)) {
            $rows = count($rawScheme);
            $seats = $rows > 0 ? count($rawScheme[0]) : 0;

            return [
                'rows' => $rows,
                'seats' => $seats,
                'seatsGrid' => $rawScheme,
            ];
        }

        if (is_array($rawScheme)) {
            $seatsGrid = $rawScheme['seatsGrid'] ?? [];
            if (is_object($seatsGrid)) {
                $seatsGrid = (array) $seatsGrid;
            }

            $rows = (int) ($rawScheme['rows'] ?? count($seatsGrid));
            $seats = (int) ($rawScheme['seats'] ?? ($seatsGrid[0] ?? null ? count($seatsGrid[0]) : 0));

            if (!is_array($seatsGrid) || $rows === 0 || $seats === 0) {
                $seatsGrid = [];
            }

            return [
                'rows' => $rows,
                'seats' => $seats,
                'seatsGrid' => $seatsGrid,
            ];
        }

        return [
            'rows' => 0,
            'seats' => 0,
            'seatsGrid' => [],
        ];
    }
}
