<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('stock_movements', 'ris_item_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->uuid('ris_item_id')->nullable()->after('air_item_id');
            });
        }

        if (! Schema::hasIndex('stock_movements', 'stock_movements_ris_item_idx')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->index('ris_item_id', 'stock_movements_ris_item_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('stock_movements', 'stock_movements_ris_item_idx')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropIndex('stock_movements_ris_item_idx');
            });
        }

        if (Schema::hasColumn('stock_movements', 'ris_item_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropColumn('ris_item_id');
            });
        }
    }
};
