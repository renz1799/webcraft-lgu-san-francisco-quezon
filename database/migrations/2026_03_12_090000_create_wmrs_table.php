<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wmrs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('wmr_number')->nullable()->unique();
            $table->uuid('fund_cluster_id')->nullable();
            $table->string('entity_name_snapshot')->nullable();
            $table->string('fund_cluster_code_snapshot', 50)->nullable();
            $table->string('place_of_storage')->nullable();
            $table->date('report_date')->nullable();
            $table->string('status')->default('draft');
            $table->text('remarks')->nullable();

            $table->string('custodian_name')->nullable();
            $table->string('custodian_designation')->nullable();
            $table->date('custodian_date')->nullable();

            $table->string('approved_by_name')->nullable();
            $table->string('approved_by_designation')->nullable();
            $table->date('approved_by_date')->nullable();

            $table->string('inspection_officer_name')->nullable();
            $table->string('inspection_officer_designation')->nullable();
            $table->date('inspection_officer_date')->nullable();

            $table->string('witness_name')->nullable();
            $table->string('witness_designation')->nullable();
            $table->date('witness_date')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'report_date']);
            $table->index(['fund_cluster_id', 'report_date']);

            $table->foreign('fund_cluster_id')
                ->references('id')
                ->on('fund_clusters')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wmrs');
    }
};
