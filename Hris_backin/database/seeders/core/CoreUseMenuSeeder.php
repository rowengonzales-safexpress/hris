<?php

namespace Database\Seeders\core;

use App\Models\Core\UserMenu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CoreUseMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usermenus = $this->importMenuFromCSV();

        if (!empty($usermenus)) {
            // Get all existing menu IDs
            $existingMenuIds = DB::table('core_menu')->pluck('id')->toArray();

            // Filter usermenus to only include those with existing menu_ids
            $validUsermenus = array_filter($usermenus, function($usermenu) use ($existingMenuIds) {
                return in_array($usermenu['menu_id'], $existingMenuIds);
            });

            // Insert only valid records
            UserMenu::insert(array_values($validUsermenus));

            // Log any skipped records for debugging
            $skipped = array_diff(
                array_column($usermenus, 'menu_id'),
                array_column($validUsermenus, 'menu_id')
            );

            if (!empty($skipped)) {
                $this->command->info('Skipped menu_ids that do not exist: ' . implode(', ', array_unique($skipped)));
            }
        }
    }

    private function importMenuFromCSV() {
        $filename = database_path('seeders/core/seedfiles/CoreUserMenus.csv');
        if (!file_exists($filename) || !$filehandle = fopen($filename, 'r')) {
            $this->command->error("CSV file not found: {$filename}");
            return [];
        }

        $tablecolumns = array('user_id','menu_id','is_manage','is_active');
        $usermenus = [];

        while($rowvalues = fgetcsv($filehandle)) {
            if ($rowvalues[0] == 'name' || empty($rowvalues)) continue;

            // Skip rows that don't have the correct number of columns
            if (count($rowvalues) !== count($tablecolumns)) {
                continue;
            }

            $usermenus[] = array_combine($tablecolumns, $rowvalues);
        }

        fclose($filehandle);
        return $usermenus;
    }
}
