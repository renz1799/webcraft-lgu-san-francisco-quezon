<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('air_item_components', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('air_item_id');
            $table->uuid('client_request_id')->nullable();
            $table->unsignedInteger('line_no')->default(1);
            $table->string('name', 255);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('unit', 50)->nullable();
            $table->decimal('component_cost', 15, 2)->default(0);
            $table->string('serial_number', 255)->nullable();
            $table->string('condition', 255)->nullable();
            $table->boolean('is_present')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['air_item_id', 'line_no'], 'air_item_components_item_line_idx');
            $table->index('client_request_id', 'air_item_components_client_request_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('air_item_components');
    }
};
