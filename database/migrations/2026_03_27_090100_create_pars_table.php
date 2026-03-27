<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pars')) {
            return;
        }

        Schema::create('pars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('par_number', 60)->nullable()->unique();
            $table->uuid('department_id')->nullable();
            $table->uuid('fund_source_id')->nullable();
            $table->string('person_accountable')->nullable();
            $table->string('received_by_position', 120)->nullable();
            $table->date('received_by_date')->nullable();
            $table->string('issued_by_name', 255)->nullable();
            $table->string('issued_by_position', 120)->nullable();
            $table->string('issued_by_office', 120)->nullable();
            $table->date('issued_by_date')->nullable();
            $table->date('issued_date')->nullable();
            $table->string('status', 40)->default('draft');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['department_id', 'issued_date']);
            $table->index(['fund_source_id']);
            $table->index(['status', 'issued_date']);

            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete();

            $table->foreign('fund_source_id')
                ->references('id')
                ->on('fund_sources')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pars');
    }
};
