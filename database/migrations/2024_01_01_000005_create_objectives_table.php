<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObjectivesTable extends Migration
{
    public function up()
    {
        Schema::create('objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('remi_id')->nullable()->constrained('remis')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('quarter', 12);
            $table->enum('calculation_mode', ['automatic', 'manual'])->default('automatic');
            $table->unsignedTinyInteger('manual_progress')->nullable();
            $table->string('manual_status', 32)->nullable();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->string('status', 32)->default('');
            $table->timestamps();

            $table->index(['company_id', 'quarter']);
            $table->index('department_id');
            $table->index('remi_id');
            $table->index('owner_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('objectives');
    }
}
