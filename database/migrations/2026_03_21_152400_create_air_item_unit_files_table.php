<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('air_item_unit_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_item_unit_id');
            $table->string('driver', 20)->default('public');
            $table->string('path');
            $table->string('type', 30)->default('photo');
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->string('original_name')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('caption')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('air_item_unit_id');
            $table->index('type');
            $table->index('is_primary');

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
            Schema::dropIfExists('air_item_unit_files');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};
