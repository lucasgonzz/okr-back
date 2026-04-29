<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['super_admin', 'admin', 'user'])->default('user');
            $table->string('avatar')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['company_id', 'role']);
            $table->index('department_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
