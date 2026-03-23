<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicatePairs = DB::table('user_modules')
            ->select('user_id', 'module_id', DB::raw('COUNT(*) as duplicate_count'))
            ->groupBy('user_id', 'module_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($duplicatePairs > 0) {
            throw new RuntimeException('Cannot add unique(user_id, module_id) to user_modules until duplicate memberships are cleaned up.');
        }

        Schema::table('user_modules', function (Blueprint $table) {
            $table->dropUnique('user_modules_user_module_department_unique');
            $table->unique(['user_id', 'module_id'], 'user_modules_user_module_unique');
        });
    }

    public function down(): void
    {
        Schema::table('user_modules', function (Blueprint $table) {
            $table->dropUnique('user_modules_user_module_unique');
            $table->unique(
                ['user_id', 'module_id', 'department_id'],
                'user_modules_user_module_department_unique'
            );
        });
    }
};
