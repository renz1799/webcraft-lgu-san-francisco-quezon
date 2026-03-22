<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->string('type', 30)->default('business')->after('name');
            $table->index('type');
        });

        DB::table('modules')
            ->where('code', 'CORE')
            ->update(['type' => 'platform']);

        DB::table('modules')
            ->where('code', 'TASKS')
            ->update(['type' => 'support']);

        DB::table('modules')
            ->whereNull('type')
            ->orWhere('type', '')
            ->update(['type' => 'business']);
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn('type');
        });
    }
};
