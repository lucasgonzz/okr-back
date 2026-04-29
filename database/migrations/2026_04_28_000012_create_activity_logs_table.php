<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogsTable extends Migration
{
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('operation', 20);
            $table->string('title');
            $table->string('resource_type', 80);
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->unsignedBigInteger('objective_id')->nullable();
            $table->unsignedBigInteger('key_result_id')->nullable();
            $table->json('changes');
            $table->timestamp('created_at')->useCurrent();

            $table->index('company_id');
            $table->index('user_id');
            $table->index('resource_type');
            $table->index('resource_id');
            $table->index('objective_id');
            $table->index('key_result_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
}
