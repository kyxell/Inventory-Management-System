<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username', 50)->nullable()->unique();
            }
            if (!Schema::hasColumn('users', 'password_hash')) {
                $table->string('password_hash')->nullable();
            }
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name', 50)->nullable();
            }
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name', 50)->nullable();
            }
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->unsignedInteger('role_id')->nullable();
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (!Schema::hasColumn('users', 'last_login')) {
                $table->timestamp('last_login')->nullable();
            }
            if (!Schema::hasColumn('users', 'name')) {
                $table->string('name')->nullable();
            }
            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'password')) {
                $table->string('password')->nullable();
            }
            if (!Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken();
            }
            if (!Schema::hasColumn('users', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
    }
};