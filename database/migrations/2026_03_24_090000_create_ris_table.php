<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ris', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_id')->nullable()->unique();
            $table->string('ris_number')->nullable()->unique();
            $table->date('ris_date')->nullable();
            $table->uuid('requesting_department_id')->nullable();
            $table->string('requesting_department_name_snapshot')->nullable();
            $table->string('requesting_department_code_snapshot')->nullable();
            $table->string('fund')->nullable();
            $table->uuid('fund_source_id')->nullable();
            $table->string('fpp_code')->nullable();
            $table->string('division')->nullable();
            $table->string('responsibility_center_code')->nullable();
            $table->string('status')->default('draft');
            $table->string('submitted_by_name')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->string('rejected_by_name')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->string('requested_by_name')->nullable();
            $table->string('requested_by_designation')->nullable();
            $table->date('requested_by_date')->nullable();
            $table->string('approved_by_name')->nullable();
            $table->string('approved_by_designation')->nullable();
            $table->date('approved_by_date')->nullable();
            $table->string('issued_by_name')->nullable();
            $table->string('issued_by_designation')->nullable();
            $table->date('issued_by_date')->nullable();
            $table->string('received_by_name')->nullable();
            $table->string('received_by_designation')->nullable();
            $table->date('received_by_date')->nullable();
            $table->text('purpose')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('requesting_department_id');
            $table->index('fund_source_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ris');
    }
};
