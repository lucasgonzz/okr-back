<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeyResultsTable extends Migration
{
    public function up()
    {
        Schema::create('key_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objective_id')->constrained('objectives')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('target', 18, 4)->default(0);
            $table->decimal('current_value', 18, 4)->default(0);
            $table->decimal('start_value', 18, 4)->default(0);
            $table->string('unit', 32)->default('');
            $table->string('status', 32)->default('');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->boolean('sums_to_objective')->default(true);
            $table->text('blockers')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->index('objective_id');
            $table->index('owner_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('key_results');
    }
}
