<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\BlocksChangesWhenSalesOpen;
use Illuminate\Http\Request;
use App\Models\Hall;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HallSchemeController extends Controller
{
    use BlocksChangesWhenSalesOpen;

    public function save(Request $request, $hallId)
    {
        if ($response = $this->denyIfSalesOpen($request)) {
            return $response;
        }

        $hall = Hall::findOrFail($hallId);
        $data = $request->json()->all();

        if (isset($data[0]) && is_array($data[0])) {
            $data = $data[0];
        }

        $validator = Validator::make(
            $data,
            [
                'rows' => ['required', 'integer', 'min:1', 'max:50'],
                'seats' => ['required', 'integer', 'min:1', 'max:50'],
                'seatsGrid' => ['required', 'array'],
                'seatsGrid.*' => ['required', 'array'],
                'seatsGrid.*.*' => ['required', 'string', Rule::in(['standart', 'vip', 'disabled'])],
            ],
            [
                'rows.required' => 'Укажите количество рядов.',
                'rows.integer' => 'Количество рядов должно быть целым числом.',
                'rows.min' => 'Количество рядов должно быть больше 0.',
                'rows.max' => 'Количество рядов не должно превышать 50.',
                'seats.required' => 'Укажите количество мест в ряду.',
                'seats.integer' => 'Количество мест в ряду должно быть целым числом.',
                'seats.min' => 'Количество мест в ряду должно быть больше 0.',
                'seats.max' => 'Количество мест в ряду не должно превышать 50.',
                'seatsGrid.required' => 'Передана пустая схема зала.',
                'seatsGrid.array' => 'Схема зала должна быть массивом.',
                'seatsGrid.*.*.in' => 'В схеме зала передан недопустимый тип места.',
            ]
        );

        $validator->after(function ($validator) use ($data) {
            if (!isset($data['rows'], $data['seats'], $data['seatsGrid']) || !is_array($data['seatsGrid'])) {
                return;
            }

            $rows = (int) $data['rows'];
            $seats = (int) $data['seats'];
            $grid = $data['seatsGrid'];

            if (count($grid) !== $rows) {
                $validator->errors()->add('seatsGrid', 'Количество рядов в схеме не совпадает со значением rows.');
            }

            foreach ($grid as $index => $row) {
                if (!is_array($row) || count($row) !== $seats) {
                    $validator->errors()->add("seatsGrid.$index", 'Количество мест в ряду не совпадает со значением seats.');
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
        $hall->scheme_json = [
            'rows' => (int) $validated['rows'],
            'seats' => (int) $validated['seats'],
            'seatsGrid' => $validated['seatsGrid'],
        ];
        $hall->save();

        return response()->json(['success' => true]);
    }



    public function show($hallId) {
        $hall = Hall::findOrFail($hallId);
        return response()->json($hall->scheme_json);
    }
    

}
