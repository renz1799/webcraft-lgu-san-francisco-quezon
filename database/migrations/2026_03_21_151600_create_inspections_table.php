<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inspector_user_id');
            $table->uuid('reviewer_user_id')->nullable();
            $table->string('status', 50)->default('draft');
            $table->uuid('department_id')->nullable();
            $table->uuid('item_id')->nullable();
            $table->string('office_department', 255)->nullable();
            $table->string('accountable_officer', 255)->nullable();
            $table->string('dv_number', 120)->nullable();
            $table->string('po_number', 120)->nullable();
            $table->text('observed_description')->nullable();
            $table->string('item_name', 255)->nullable();
            $table->string('brand', 255)->nullable();
            $table->string('model', 255)->nullable();
            $table->string('serial_number', 255)->nullable();
            $table->decimal('acquisition_cost', 15, 2)->nullable();
            $table->date('acquisition_date')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('condition', 100)->default('good');
            $table->string('drive_folder_id', 120)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('po_number');
            $table->index('drive_folder_id');
            $table->index('department_id');
            $table->index('item_id');
            $table->index('inspector_user_id');
            $table->index('reviewer_user_id');

            $table->foreign('inspector_user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('reviewer_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete();

            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
