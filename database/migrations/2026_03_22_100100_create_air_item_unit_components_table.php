<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('air_item_unit_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_item_unit_id');
            $table->unsignedInteger('line_no')->default(1);
            $table->string('name', 255);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('unit', 50)->nullable();
            $table->decimal('component_cost', 15, 2)->default(0);
            $table->string('serial_number', 255)->nullable();
            $table->string('condition', 100)->nullable();
            $table->boolean('is_present')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('air_item_unit_id');
            $table->index('line_no');
            $table->index('is_present');

            $table->foreign('air_item_unit_id')
                ->references('id')
                ->on('air_item_units')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            Schema::dropIfExists('air_item_unit_components');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};
