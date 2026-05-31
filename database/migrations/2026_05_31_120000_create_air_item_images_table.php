<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('air_item_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_item_id');
            $table->string('storage_provider', 50)->default('google');
            $table->string('storage_disk', 50)->nullable();
            $table->string('storage_path');
            $table->string('external_file_id', 255)->nullable();
            $table->string('original_name')->nullable();
            $table->string('stored_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('air_item_id');
            $table->index('external_file_id');

            $table->foreign('air_item_id')
                ->references('id')
                ->on('air_items')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            Schema::dropIfExists('air_item_images');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};
