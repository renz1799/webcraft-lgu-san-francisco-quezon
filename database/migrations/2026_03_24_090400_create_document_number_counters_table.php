<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('document_number_counters')) {
            return;
        }

        Schema::create('document_number_counters', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 50);
            $table->unsignedInteger('year');
            $table->string('period_key', 20)->nullable();
            $table->string('scope_key', 120)->nullable();
            $table->unsignedInteger('last_seq')->default(0);
            $table->timestamps();

            $table->unique(
                ['document_type', 'year', 'period_key', 'scope_key'],
                'doc_number_counters_unique_scope'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_number_counters');
    }
};
