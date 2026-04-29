<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuartersTable extends Migration
{
    public function up()
    {
        Schema::create('quarters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name', 32);
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('position');
            $table->timestamps();

            $table->unique(['company_id', 'name']);
            $table->index(['company_id', 'year', 'position']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('quarters');
    }
}
