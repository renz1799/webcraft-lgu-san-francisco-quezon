<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type_code', 50)->unique();
            $table->string('type_name', 255);
            $table->timestamps();
            $table->softDeletes();

            $table->index('type_name');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            Schema::dropIfExists('asset_types');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};
