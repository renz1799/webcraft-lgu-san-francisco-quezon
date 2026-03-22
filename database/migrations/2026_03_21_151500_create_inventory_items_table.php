<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->uuid('air_item_unit_id')->nullable();
            $table->uuid('department_id')->nullable();
            $table->uuid('fund_source_id')->nullable();
            $table->string('property_number', 120)->nullable();
            $table->date('acquisition_date')->nullable();
            $table->decimal('acquisition_cost', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('unit', 50)->nullable();
            $table->string('stock_number', 120)->nullable();
            $table->unsignedInteger('service_life')->nullable();
            $table->boolean('is_ics')->default(false);
            $table->string('accountable_officer', 255)->nullable();
            $table->uuid('accountable_officer_id')->nullable();
            $table->string('custody_state', 20)->default('pool');
            $table->string('status', 100)->nullable();
            $table->string('condition', 100)->nullable();
            $table->string('brand', 255)->nullable();
            $table->string('model', 255)->nullable();
            $table->string('serial_number', 255)->nullable();
            $table->string('po_number', 120)->nullable();
            $table->string('drive_folder_id', 120)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreign('fund_source_id')
                ->references('id')
                ->on('fund_sources')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreign('accountable_officer_id')
                ->references('id')
                ->on('accountable_officers')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->unique('air_item_unit_id');
            $table->index(['item_id', 'department_id']);
            $table->index('fund_source_id');
            $table->index('accountable_officer_id');
            $table->index('property_number');
            $table->index('po_number');
            $table->index('serial_number');
            $table->index('stock_number');
            $table->index('status');
            $table->index('condition');
            $table->index('custody_state');
            $table->index('is_ics');
            $table->index('drive_folder_id');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            Schema::dropIfExists('inventory_items');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};
