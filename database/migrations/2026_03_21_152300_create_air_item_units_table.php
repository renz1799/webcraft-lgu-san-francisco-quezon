<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('air_item_units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_item_id');
            $table->uuid('inventory_item_id')->nullable();
            $table->string('brand', 255)->nullable();
            $table->string('model', 255)->nullable();
            $table->string('serial_number', 255)->nullable();
            $table->string('property_number', 255)->nullable();
            $table->string('condition_status', 100)->nullable();
            $table->text('condition_notes')->nullable();
            $table->string('drive_folder_id', 120)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('air_item_id');
            $table->index('inventory_item_id');
            $table->index('serial_number');
            $table->index('property_number');
            $table->index('condition_status');

            $table->foreign('air_item_id')
                ->references('id')
                ->on('air_items')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('inventory_item_id')
                ->references('id')
                ->on('inventory_items')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            Schema::dropIfExists('air_item_units');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};
