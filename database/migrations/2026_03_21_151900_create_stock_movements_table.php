<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->uuid('fund_source_id')->nullable();
            $table->string('movement_type', 30);
            $table->integer('qty');
            $table->string('reference_type', 50)->nullable();
            $table->uuid('reference_id')->nullable();
            $table->uuid('air_item_id')->nullable();
            $table->uuid('ris_item_id')->nullable();
            $table->dateTime('occurred_at');
            $table->string('created_by_name', 255)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('fund_source_id')
                ->references('id')
                ->on('fund_sources')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index(['item_id', 'occurred_at'], 'stock_movements_item_date_idx');
            $table->index(['reference_type', 'reference_id'], 'stock_movements_reference_idx');
            $table->index(['item_id', 'fund_source_id'], 'stock_movements_item_fund_idx');
            $table->index('movement_type', 'stock_movements_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
