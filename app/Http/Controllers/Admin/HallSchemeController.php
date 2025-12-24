<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hall;

class HallSchemeController extends Controller
{
    public function save(Request $request, $hallId)
    {
        $hall = Hall::findOrFail($hallId);
        $data = $request->json()->all();

        if (isset($data[0]) && is_array($data[0])) {
            $data = $data[0];
        }

        $hall->scheme_json = $data;
        $hall->save();
        return response()->json(['success' => true]);
    }



    public function show($hallId) {
        $hall = Hall::findOrFail($hallId);
        return response()->json($hall->scheme_json);
    }
    

}

