<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use App\Models\ChairType;
use App\Models\ChairPrice;
use App\Models\Movie;
use App\Models\Seance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class HallController extends Controller
{
    public function index()
    {
        // Все залы
        $halls = Hall::orderBy('id')->get();

        // Схемы залов
        $hallSchemes = [];
        foreach ($halls as $hall) {
            // Проверяем scheme_json
            $scheme = $hall->scheme_json;
        
            // Если это массив и не пустой - сохраняем
            if (is_array($scheme) && !empty($scheme)) {
                $hallSchemes[$hall->id] = $scheme;
            } else {
                // Иначе пустой объект (не массив!)
                $hallSchemes[$hall->id] = (object)[];
            }
        }

        // Типы кресел
        if (ChairType::count() === 0) {
            ChairType::firstOrCreate(['code' => 'standart'], ['title' => 'Обычное']);
            ChairType::firstOrCreate(['code' => 'vip'], ['title' => 'VIP']);
        }
        $chairTypes = ChairType::orderBy('id')->get();

        // Цены по залам и типам
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

        $movies = Movie::orderBy('id')->get();
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

        return view('admin.index', compact(
            'halls',
            'hallSchemes',
            'movies',
            'chairTypes',
            'prices',
            'seances'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255|unique:halls,name',
            ],
            [
                'name.unique'   => 'Зал с таким названием уже существует.',
                'name.required' => 'Введите название зала.',
            ]
        );

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors(),
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $hall = Hall::create([
            'name' => $request->name,
        ]);

        if ($request->ajax()) {
            $chairTypes = ChairType::orderBy('id')->get();
            $initialPrices = [];
            foreach ($chairTypes as $type) {
                 $initialPrices[$type->code] = null;
            }

            return response()->json([
                'success' => true,
                'hall'    => [
                    'id'          => $hall->id,
                    'name'        => $hall->name,
                    'destroy_url' => route('admin.halls.destroy', $hall),
                ],
                'prices' => $initialPrices,
            ]);
        }

        return redirect()->route('admin.halls.index');
    }

    
    public function destroy(Request $request, $id)
    {
        $hall = Hall::findOrFail($id);
        $hall->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
            ]);
        }

        return redirect()
            ->route('admin.halls.index')
            ->with('success', 'Зал удалён!');
    }
}
