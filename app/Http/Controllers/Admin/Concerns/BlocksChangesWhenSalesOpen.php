<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Publication;
use Illuminate\Http\Request;

trait BlocksChangesWhenSalesOpen
{
    protected function denyIfSalesOpen(Request $request)
    {
        $salesOpen = Publication::query()->active()->exists();
        if (!$salesOpen) {
            return null;
        }

        $message = 'Продажи открыты. Изменение залов, фильмов и сеансов недоступно до закрытия продаж.';
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => [
                    'sales' => [$message],
                ],
            ], 423);
        }

        return redirect()
            ->back()
            ->withErrors(['sales' => $message]);
    }
}
