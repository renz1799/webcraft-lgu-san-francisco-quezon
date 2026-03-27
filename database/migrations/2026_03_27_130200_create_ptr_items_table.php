<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ptr_items')) {
            return;
        }

        Schema::create('ptr_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('ptr_id');
            $table->uuid('inventory_item_id');

            $table->unsignedInteger('line_no')->default(1);

            $table->date('date_acquired_snapshot')->nullable();
            $table->string('property_number_snapshot', 120)->nullable();
            $table->text('description_snapshot')->nullable();
            $table->decimal('amount_snapshot', 15, 2)->nullable();
            $table->string('condition_snapshot', 120)->nullable();
            $table->string('item_name_snapshot')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['ptr_id']);
            $table->index(['inventory_item_id']);
            $table->index(['ptr_id', 'deleted_at']);
            $table->index(['inventory_item_id', 'deleted_at']);
            $table->index(['ptr_id', 'line_no']);

            $table->unique(['ptr_id', 'inventory_item_id', 'deleted_at'], 'ptr_items_unique_active');

            $table->foreign('ptr_id')
                ->references('id')
                ->on('ptrs')
                ->cascadeOnDelete();

            $table->foreign('inventory_item_id')
                ->references('id')
                ->on('inventory_items')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ptr_items');
    }
};
