<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\BlocksChangesWhenSalesOpen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ChairType;
use Illuminate\Support\Facades\Validator;

class SetPricesController extends Controller
{
    use BlocksChangesWhenSalesOpen;

    public function index()
    {
        return redirect()->route('admin.halls.index');
    }

    public function update(Request $request, $hallId)
    {
        if ($response = $this->denyIfSalesOpen($request)) {
            return $response;
        }

        $hallId = (int) $hallId;

        $payload = [
            'hall_id' => $hallId,
            'prices' => [
                'standart' => $request->input(
                    'prices.standart',
                    $request->input(
                        'standart',
                        $request->input('standard_price')
                    )
                ),
                'vip' => $request->input(
                    'prices.vip',
                    $request->input(
                        'vip',
                        $request->input('vip_price')
                    )
                ),
            ],
        ];

        $validator = Validator::make(
            $payload,
            [
                'hall_id' => ['required', 'integer', 'exists:halls,id'],
                'prices.standart' => ['required', 'integer', 'min:1'],
                'prices.vip' => ['required', 'integer', 'min:1'],
            ],
            [
                'hall_id.exists' => 'Выбранный зал не найден.',
                'prices.standart.required' => 'Укажите стоимость обычного места.',
                'prices.standart.integer' => 'Стоимость обычного места должна быть целым числом.',
                'prices.standart.min' => 'Стоимость обычного места должна быть больше 0.',
                'prices.vip.required' => 'Укажите стоимость VIP-места.',
                'prices.vip.integer' => 'Стоимость VIP-места должна быть целым числом.',
                'prices.vip.min' => 'Стоимость VIP-места должна быть больше 0.',
            ]
        );

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $standart = (int) $validated['prices']['standart'];
        $vip = (int) $validated['prices']['vip'];

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
