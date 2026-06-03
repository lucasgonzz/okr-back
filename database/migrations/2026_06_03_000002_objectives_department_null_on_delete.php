<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ObjectivesDepartmentNullOnDelete extends Migration
{
    public function up()
    {
        Schema::table('objectives', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
        });

        Schema::table('objectives', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->change();
            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('objectives', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
        });

        Schema::table('objectives', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable(false)->change();
            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->cascadeOnDelete();
        });
    }
}
