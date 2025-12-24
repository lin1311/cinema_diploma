<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up()
    {
        Schema::table('halls', function (Blueprint $table) {
            if (!Schema::hasColumn('halls', 'scheme_json')) {
                $table->json('scheme_json')->nullable()->after('name');
            }
        });
    }

    public function down()
    {
        Schema::table('halls', function (Blueprint $table) {
            if (Schema::hasColumn('halls', 'scheme_json')) {
                $table->dropColumn('scheme_json');
            }
        });
    }
};

