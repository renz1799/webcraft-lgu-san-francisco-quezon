<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('type', 50)->nullable(); // office, division, section, unit

            $table->uuid('parent_department_id')->nullable();
            $table->uuid('head_user_id')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('type');
            $table->index('parent_department_id');
            $table->index('head_user_id');

            $table->foreign('parent_department_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};