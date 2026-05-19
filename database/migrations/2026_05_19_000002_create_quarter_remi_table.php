<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuarterRemiTable extends Migration
{
    public function up()
    {
        Schema::create('quarter_remi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quarter_id')->constrained('quarters')->cascadeOnDelete();
            $table->foreignId('remi_id')->constrained('remis')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['quarter_id', 'remi_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('quarter_remi');
    }
}
