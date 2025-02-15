<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->string('year', 4)->storedAs("SUBSTR(`date`, 1, 4)")->after('date');
            $table->string('month', 2)->storedAs("SUBSTR(`date`, 6, 2)")->after('year');
            $table->string('day', 2)->storedAs("SUBSTR(`date`, 9, 2)")->after('month');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('year');
            $table->dropColumn('month');
            $table->dropColumn('day');
        });
    }
};
