<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\tracking;
use Illuminate\Database\Seeder;
use App\Models\Core\CoreAppUser;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Admin\AppsystemUser;
use Database\Seeders\Pism\PismSeeder;
use Database\Seeders\Sqdcm\SqdcmSeeder;
use Database\Seeders\core\CoreAppSeeder;
use Database\Seeders\core\CoreRoleSeeder;
use Database\Seeders\core\CoreUserSeeder;
use Database\Seeders\task\ThemeVSCSeeder;
use Database\Seeders\task\ThemesVSCSeeder;
use Database\Seeders\core\CoreBranchSeeder;
use Database\Seeders\core\CoreAppUserSeeder;
use Database\Seeders\core\CoreUseMenuSeeder;
use Database\Seeders\core\CoreRoleMenuSeeder;
use Database\Seeders\core\CoreBranchUsersSeeder;
use Database\Seeders\tracking\TmsTrackingSeeder;
use Database\Seeders\core\CoreMenuSeeder as CoreCoreMenuSeeder;
use Database\Seeders\task\TaskTypeSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       $this->call([

            CoreAppSeeder::class,
            CoreRoleSeeder::class,
            CoreCoreMenuSeeder::class,
            // Seed branches BEFORE users to satisfy FK constraints
            CoreBranchSeeder::class,
            // Seed users AFTER branches are available
            CoreUserSeeder::class,
            // Link branches and users after both exist
            CoreBranchUsersSeeder::class,
            // Tracking seeders can run after core data is present
            TmsTrackingSeeder::class,
            CoreRoleMenuSeeder::class,
            CoreAppUserSeeder::class,
            CoreUseMenuSeeder::class,
            RoleSeeder::class,
            CoreTransactionCodeSeeder::class,
            SqdcmSeeder::class,
            ThemesVSCSeeder::class,
            TaskTypeSeeder::class,
        ]);

    }
}
