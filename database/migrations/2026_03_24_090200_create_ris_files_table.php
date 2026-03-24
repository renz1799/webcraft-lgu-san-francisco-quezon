<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ris_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ris_id');
            $table->string('driver');
            $table->string('path');
            $table->string('type')->default('other');
            $table->boolean('is_primary')->default(false);
            $table->integer('position')->default(0);
            $table->string('original_name')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('caption')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('ris_id');
            $table->index(['driver', 'path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ris_files');
    }
};
