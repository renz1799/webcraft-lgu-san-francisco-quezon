<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ics_items')) {
            return;
        }

        Schema::create('ics_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('ics_id');
            $table->uuid('inventory_item_id');

            $table->unsignedInteger('line_no')->default(1);
            $table->unsignedInteger('quantity')->default(1);

            $table->string('unit_snapshot', 50)->nullable();
            $table->decimal('unit_cost_snapshot', 15, 2)->nullable();
            $table->decimal('total_cost_snapshot', 15, 2)->nullable();
            $table->text('description_snapshot')->nullable();
            $table->string('inventory_item_no_snapshot', 120)->nullable();
            $table->string('estimated_useful_life_snapshot', 120)->nullable();
            $table->string('item_name_snapshot')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['ics_id']);
            $table->index(['inventory_item_id']);
            $table->index(['ics_id', 'deleted_at']);
            $table->index(['inventory_item_id', 'deleted_at']);
            $table->index(['ics_id', 'line_no']);

            $table->foreign('ics_id')
                ->references('id')
                ->on('ics')
                ->cascadeOnDelete();

            $table->foreign('inventory_item_id')
                ->references('id')
                ->on('inventory_items')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ics_items');
    }
};
