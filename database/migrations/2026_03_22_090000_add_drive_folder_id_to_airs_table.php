<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('airs', function (Blueprint $table) {
            if (! Schema::hasColumn('airs', 'drive_folder_id')) {
                $table->string('drive_folder_id', 120)->nullable()->after('accepted_by_name');
                $table->index('drive_folder_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('airs', function (Blueprint $table) {
            if (Schema::hasColumn('airs', 'drive_folder_id')) {
                $table->dropIndex(['drive_folder_id']);
                $table->dropColumn('drive_folder_id');
            }
        });
    }
};
