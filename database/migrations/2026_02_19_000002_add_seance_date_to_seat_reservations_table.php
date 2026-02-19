<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('seat_reservations', function (Blueprint $table) {
            $table->date('seance_date')->nullable()->after('seance_id');
        });

        DB::table('seat_reservations')
            ->whereNull('seance_date')
            ->update(['seance_date' => Carbon::today()->toDateString()]);

        Schema::table('seat_reservations', function (Blueprint $table) {
            $table->dropUnique(['seance_id', 'row', 'seat']);
            $table->unique(['seance_id', 'seance_date', 'row', 'seat']);
        });
    }

    public function down(): void
    {
        Schema::table('seat_reservations', function (Blueprint $table) {
            $table->dropUnique(['seance_id', 'seance_date', 'row', 'seat']);
            $table->unique(['seance_id', 'row', 'seat']);
            $table->dropColumn('seance_date');
        });
    }
};
