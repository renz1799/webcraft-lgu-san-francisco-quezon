<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->uuid('fund_source_id')->nullable();
            $table->integer('on_hand')->default(0);
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

            $table->unique(['item_id', 'fund_source_id'], 'stocks_item_fund_unique');
            $table->index('item_id');
            $table->index('fund_source_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
