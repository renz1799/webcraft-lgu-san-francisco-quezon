<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('asset_id');
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->string('base_unit', 50)->nullable();
            $table->string('item_identification', 255)->nullable();
            $table->string('major_sub_account_group', 255)->nullable();
            $table->string('tracking_type', 25);
            $table->boolean('requires_serial')->default(false);
            $table->boolean('is_semi_expendable')->default(false);
            $table->boolean('is_selected')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('asset_id');
            $table->index('tracking_type');
            $table->index('requires_serial');
            $table->index('is_semi_expendable');

            $table->foreign('asset_id')
                ->references('id')
                ->on('asset_categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
