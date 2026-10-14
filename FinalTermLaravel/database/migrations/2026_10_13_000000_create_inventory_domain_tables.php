<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 50)->unique();
                $table->string('description', 255)->nullable();
            });
        }

        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->unsignedInteger('category_id')->nullable()->index();
                $table->decimal('unit_price', 10, 2)->default(0);
                $table->decimal('cost_price', 10, 2)->default(0);
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('inventory_levels')) {
            Schema::create('inventory_levels', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('product_id')->unique();
                $table->integer('quantity')->default(0);
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            });
        }

        if (!Schema::hasTable('inventory_logs')) {
            Schema::create('inventory_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('product_id')->index();
                $table->integer('quantity_change');
                $table->integer('new_quantity');
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
    }
};