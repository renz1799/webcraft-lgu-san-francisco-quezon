<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ptrs')) {
            return;
        }

        Schema::create('ptrs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('ptr_number')->nullable()->unique();
            $table->date('transfer_date')->nullable();
            $table->string('status')->default('draft');

            $table->uuid('from_department_id')->nullable();
            $table->string('from_accountable_officer')->nullable();
            $table->uuid('from_fund_source_id')->nullable();

            $table->uuid('to_department_id')->nullable();
            $table->string('to_accountable_officer')->nullable();
            $table->uuid('to_fund_source_id')->nullable();

            $table->string('transfer_type', 50)->nullable();
            $table->string('transfer_type_other')->nullable();
            $table->text('reason_for_transfer')->nullable();
            $table->text('remarks')->nullable();

            $table->string('entity_name_snapshot')->nullable();
            $table->string('header_fund_cluster_code_snapshot', 50)->nullable();
            $table->string('from_department_snapshot')->nullable();
            $table->string('from_fund_cluster_code_snapshot', 50)->nullable();
            $table->string('to_department_snapshot')->nullable();
            $table->string('to_fund_cluster_code_snapshot', 50)->nullable();

            $table->string('approved_by_name')->nullable();
            $table->string('approved_by_designation')->nullable();
            $table->date('approved_by_date')->nullable();

            $table->string('released_by_name')->nullable();
            $table->string('released_by_designation')->nullable();
            $table->date('released_by_date')->nullable();

            $table->string('received_by_name')->nullable();
            $table->string('received_by_designation')->nullable();
            $table->date('received_by_date')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'transfer_date']);
            $table->index(['from_department_id', 'transfer_date']);
            $table->index(['to_department_id', 'transfer_date']);
            $table->index(['from_fund_source_id', 'transfer_date']);
            $table->index(['to_fund_source_id', 'transfer_date']);

            $table->foreign('from_department_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete();

            $table->foreign('to_department_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete();

            $table->foreign('from_fund_source_id')
                ->references('id')
                ->on('fund_sources')
                ->nullOnDelete();

            $table->foreign('to_fund_source_id')
                ->references('id')
                ->on('fund_sources')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ptrs');
    }
};
