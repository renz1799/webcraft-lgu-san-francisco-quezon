<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ris_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ris_id');
            $table->uuid('item_id');
            $table->integer('line_no')->nullable();
            $table->string('stock_no_snapshot')->nullable();
            $table->text('description_snapshot')->nullable();
            $table->string('unit_snapshot')->nullable();
            $table->integer('qty_requested');
            $table->integer('qty_issued')->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('ris_id');
            $table->index('item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ris_items');
    }
};
