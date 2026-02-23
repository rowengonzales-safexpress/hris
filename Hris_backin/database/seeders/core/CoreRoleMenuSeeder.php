<?php

namespace Database\Seeders\core;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CoreRoleMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rolemenus = $this->importMenuFromCSV();

        if (!empty($rolemenus)) {
            // Get all existing menu IDs
            $existingMenuIds = DB::table('core_menu')->pluck('id')->toArray();
            $existingRoleIds = DB::table('core_role')->pluck('id')->toArray();

            // Filter rolemenus to only include those with existing menu_ids and role_ids
            $validRoleMenus = array_filter($rolemenus, function($rolemenu) use ($existingMenuIds, $existingRoleIds) {
                return in_array($rolemenu['menu_id'], $existingMenuIds) && in_array($rolemenu['role_id'], $existingRoleIds);
            });

            // Prepare data for insertion (adding timestamps if needed, though typically seeders might skip them or set them now)
            $insertData = array_map(function($item) {
                return [
                    'role_id' => $item['role_id'],
                    'app_id' => $item['app_id'],
                    'menu_id' => $item['menu_id'],
                    'permission' => $item['permission'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $validRoleMenus);

            // Insert only valid records
            if (!empty($insertData)) {
                DB::table('core_rolemenu')->insert($insertData);
            }

            // Log any skipped records for debugging
            $skippedMenus = array_diff(
                array_column($rolemenus, 'menu_id'),
                array_column($validRoleMenus, 'menu_id')
            );
            
            $skippedRoles = array_diff(
                array_column($rolemenus, 'role_id'),
                array_column($validRoleMenus, 'role_id')
            );

            if (!empty($skippedMenus)) {
                $this->command->info('Skipped menu_ids that do not exist: ' . implode(', ', array_unique($skippedMenus)));
            }
            if (!empty($skippedRoles)) {
                $this->command->info('Skipped role_ids that do not exist: ' . implode(', ', array_unique($skippedRoles)));
            }
        }
    }

    private function importMenuFromCSV() {
        $filename = database_path('seeders/core/seedfiles/CoreRoleMenu.csv');
        if (!file_exists($filename) || !$filehandle = fopen($filename, 'r')) {
            $this->command->error("CSV file not found: {$filename}");
            return [];
        }

        $tablecolumns = array('role_id','app_id','menu_id','permission');
        $rolemenus = [];

        while($rowvalues = fgetcsv($filehandle)) {
            if ($rowvalues[0] == 'role_id' || empty($rowvalues)) continue;

            // Skip rows that don't have the correct number of columns
            if (count($rowvalues) !== count($tablecolumns)) {
                continue;
            }

            $rolemenus[] = array_combine($tablecolumns, $rowvalues);
        }

        fclose($filehandle);
        return $rolemenus;
    }
}
