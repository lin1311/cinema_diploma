<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Hall;
use App\Models\ChairType;

class SetPricesController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.halls.index');
    }

    public function update(Request $request, $hallId)
    {
        $hallId = (int) $hallId;

        $standart = $request->input(
            'prices.standart',
            $request->input(
                'standart',
                $request->input('standard_price')
            )
        );

        $vip = $request->input(
            'prices.vip',
            $request->input(
                'vip',
                $request->input('vip_price')
            )
        );

        $normalize = function ($value) {
            if ($value === null || $value === '') {
                return 0;
            }
            if (!is_numeric($value)) {
                return 0;
            }
            return (int) $value;
        };

        $standart = $normalize($standart);
        $vip = $normalize($vip);

        if (ChairType::count() === 0) {
            ChairType::firstOrCreate(['code' => 'standart'], ['title' => 'Обычное']);
            ChairType::firstOrCreate(['code' => 'vip'], ['title' => 'VIP']);
        }

        $chairTypes = ChairType::orderBy('id')->get();
        $standardType = $chairTypes->firstWhere('code', 'standart')
            ?? $chairTypes->firstWhere('code', 'standard')
            ?? $chairTypes[0] ?? null;
        $vipType = $chairTypes->firstWhere('code', 'vip')
            ?? $chairTypes[1] ?? null;

        // стандартные кресла
        if ($standardType) {
            DB::table('chair_prices')->updateOrInsert(
                [
                    'hall_id'       => $hallId,
                    'chair_type_id' => $standardType->id,
                ],
                [
                    'price'      => $standart,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        // VIP кресла
        if ($vipType) {
            DB::table('chair_prices')->updateOrInsert(
                [
                    'hall_id'       => $hallId,
                    'chair_type_id' => $vipType->id,
                ],
                [
                    'price'      => $vip,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }


        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'hall_id' => $hallId,
                'prices'  => [
                    'standart' => $standart,
                    'vip'      => $vip,
                ],
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Цены успешно сохранены!');
    }
}
