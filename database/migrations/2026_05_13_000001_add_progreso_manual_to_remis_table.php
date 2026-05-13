<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('remis', function (Blueprint $table) {
            $table->unsignedTinyInteger('progreso_manual')->default(0)->after('responsible_department_id');
        });
    }

    public function down(): void
    {
        Schema::table('remis', function (Blueprint $table) {
            $table->dropColumn('progreso_manual');
        });
    }
};
