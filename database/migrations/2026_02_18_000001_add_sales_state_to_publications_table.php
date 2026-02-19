<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            if (!Schema::hasColumn('publications', 'is_active')) {
                $table->boolean('is_active')->default(false)->after('payload');
            }

            if (!Schema::hasColumn('publications', 'opened_at')) {
                $table->timestamp('opened_at')->nullable()->after('is_active');
            }

            if (!Schema::hasColumn('publications', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('opened_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            if (Schema::hasColumn('publications', 'closed_at')) {
                $table->dropColumn('closed_at');
            }

            if (Schema::hasColumn('publications', 'opened_at')) {
                $table->dropColumn('opened_at');
            }

            if (Schema::hasColumn('publications', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
