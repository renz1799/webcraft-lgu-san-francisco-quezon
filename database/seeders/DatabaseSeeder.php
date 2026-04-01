<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Modules\GSO\AssetTypeSeeder;
use Database\Seeders\Modules\GSO\AssetCategorySeeder;
use Database\Seeders\Modules\GSO\FundClusterSeeder;
use Database\Seeders\Modules\GSO\FundSourceSeeder;
use Database\Seeders\Modules\GSO\GsoStorageSettingsSeeder;
use Database\Seeders\Modules\GSO\ItemSeeder;
use Database\Seeders\Modules\GSO\AirWithItemsSeeder;
use Database\Seeders\Modules\GSO\AirRisPrintTestSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Core / platform structural seeders
        $this->call(DepartmentSeeder::class);
        $this->call(ModuleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(PermissionsSeeder::class);

        // Shared/sample platform data
        $this->call(TaskSeeder::class);
        $this->call(NotificationSeeder::class);

        // GSO module seeders
        $this->call(AssetTypeSeeder::class);
        $this->call(AssetCategorySeeder::class);
        $this->call(FundClusterSeeder::class);
        $this->call(FundSourceSeeder::class);
        $this->call(GsoStorageSettingsSeeder::class);
      $this->call(ItemSeeder::class);
       $this->call(AirWithItemsSeeder::class);
       $this->call(AirRisPrintTestSeeder::class);
    }
}

// php artisan migrate:fresh --seed
