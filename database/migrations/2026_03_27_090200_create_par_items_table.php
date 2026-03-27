<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('par_items')) {
            return;
        }

        Schema::create('par_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('par_id');
            $table->uuid('inventory_item_id');
            $table->unsignedInteger('quantity')->default(1);
            $table->string('property_number_snapshot', 120)->nullable();
            $table->string('item_name_snapshot', 255)->nullable();
            $table->string('unit_snapshot', 50)->nullable();
            $table->decimal('amount_snapshot', 15, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['par_id']);
            $table->index(['inventory_item_id']);
            $table->index(['par_id', 'deleted_at']);
            $table->index(['inventory_item_id', 'deleted_at']);

            $table->foreign('par_id')
                ->references('id')
                ->on('pars')
                ->cascadeOnDelete();

            $table->foreign('inventory_item_id')
                ->references('id')
                ->on('inventory_items')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('par_items');
    }
};
