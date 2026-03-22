<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_item_event_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_item_event_id');
            $table->string('disk', 50)->nullable();
            $table->string('path', 255)->nullable();
            $table->string('drive_file_id', 120)->nullable();
            $table->string('drive_web_view_link', 255)->nullable();
            $table->string('original_name', 255)->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('inventory_item_event_id')
                ->references('id')
                ->on('inventory_item_events')
                ->cascadeOnDelete();

            $table->index('inventory_item_event_id');
            $table->index('drive_file_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_item_event_files');
    }
};
