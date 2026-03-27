<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('itr_items')) {
            return;
        }

        Schema::create('itr_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('itr_id');
            $table->uuid('inventory_item_id');

            $table->unsignedInteger('line_no')->default(1);
            $table->unsignedInteger('quantity')->default(1);

            $table->date('date_acquired_snapshot')->nullable();
            $table->string('inventory_item_no_snapshot', 120)->nullable();
            $table->text('description_snapshot')->nullable();
            $table->decimal('amount_snapshot', 15, 2)->nullable();
            $table->string('estimated_useful_life_snapshot', 120)->nullable();
            $table->string('condition_snapshot', 120)->nullable();
            $table->string('item_name_snapshot')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['itr_id']);
            $table->index(['inventory_item_id']);
            $table->index(['itr_id', 'deleted_at']);
            $table->index(['inventory_item_id', 'deleted_at']);
            $table->index(['itr_id', 'line_no']);

            $table->unique(['itr_id', 'inventory_item_id', 'deleted_at'], 'itr_items_unique_active');

            $table->foreign('itr_id')
                ->references('id')
                ->on('itrs')
                ->cascadeOnDelete();

            $table->foreign('inventory_item_id')
                ->references('id')
                ->on('inventory_items')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itr_items');
    }
};
