<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeyResultLogsTable extends Migration
{
    public function up()
    {
        Schema::create('key_result_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('key_result_id')->constrained('key_results')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // changes: JSON array of { field, old, new } entries for all fields modified in this action
            $table->json('changes');
            $table->timestamp('created_at')->useCurrent();

            $table->index('key_result_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('key_result_logs');
    }
}
