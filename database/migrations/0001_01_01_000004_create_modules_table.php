<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('code', 50)->unique();   // DTS, GSO, HR
            $table->string('name');                 // Document Tracking System
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->uuid('default_department_id')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('is_active');
            $table->index('default_department_id');

            $table->foreign('default_department_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
