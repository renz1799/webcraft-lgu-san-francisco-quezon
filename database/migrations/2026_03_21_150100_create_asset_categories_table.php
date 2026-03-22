<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('asset_type_id');
            $table->string('asset_code', 50);
            $table->string('asset_name', 255);
            $table->string('account_group', 255)->nullable();
            $table->boolean('is_selected')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('asset_type_id')
                ->references('id')
                ->on('asset_types')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->unique(['asset_type_id', 'asset_code']);
            $table->index(['asset_type_id', 'asset_name']);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            Schema::dropIfExists('asset_categories');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};
