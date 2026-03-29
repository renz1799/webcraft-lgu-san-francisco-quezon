<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wmr_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wmr_id');
            $table->uuid('inventory_item_id');

            $table->unsignedInteger('line_no')->default(1);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('unit_snapshot')->nullable();
            $table->string('description_snapshot')->nullable();
            $table->string('item_name_snapshot')->nullable();
            $table->string('reference_no_snapshot')->nullable();
            $table->date('date_acquired_snapshot')->nullable();
            $table->decimal('acquisition_cost_snapshot', 15, 2)->nullable();
            $table->string('condition_snapshot')->nullable();

            $table->string('disposal_method', 50)->nullable();
            $table->string('transfer_entity_name')->nullable();
            $table->string('official_receipt_no')->nullable();
            $table->date('official_receipt_date')->nullable();
            $table->decimal('official_receipt_amount', 15, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['wmr_id', 'line_no']);
            $table->index(['inventory_item_id', 'disposal_method']);

            $table->foreign('wmr_id')
                ->references('id')
                ->on('wmrs')
                ->cascadeOnDelete();

            $table->foreign('inventory_item_id')
                ->references('id')
                ->on('inventory_items')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wmr_items');
    }
};
