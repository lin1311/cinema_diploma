<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            if (!Schema::hasColumn('movies', 'duration')) {
                $table->integer('duration')->nullable()->after('description');
            }
            if (!Schema::hasColumn('movies', 'country')) {
                $table->string('country')->nullable()->after('duration');
            }
        });

        if (Schema::hasColumn('movies', 'duration_minutes') && Schema::hasColumn('movies', 'duration')) {
            DB::table('movies')
                ->whereNull('duration')
                ->update(['duration' => DB::raw('duration_minutes')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            if (Schema::hasColumn('movies', 'country')) {
                $table->dropColumn('country');
            }
            if (Schema::hasColumn('movies', 'duration')) {
                $table->dropColumn('duration');
            }
        });
    }
};
