<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ics')) {
            return;
        }

        Schema::create('ics', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('ics_number')->nullable()->unique();

            $table->uuid('department_id')->nullable();
            $table->uuid('fund_source_id')->nullable();

            $table->string('entity_name_snapshot')->nullable();
            $table->string('fund_cluster_code_snapshot', 50)->nullable();
            $table->string('fund_cluster_name_snapshot')->nullable();

            $table->date('issued_date')->nullable();

            $table->string('received_from_name')->nullable();
            $table->string('received_from_position')->nullable();
            $table->string('received_from_office')->nullable();
            $table->date('received_from_date')->nullable();

            $table->string('received_by_name')->nullable();
            $table->string('received_by_position')->nullable();
            $table->string('received_by_office')->nullable();
            $table->date('received_by_date')->nullable();

            $table->string('status')->default('draft');
            $table->text('remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['department_id', 'issued_date']);
            $table->index(['fund_source_id', 'issued_date']);
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
        Schema::dropIfExists('ics');
    }
};
