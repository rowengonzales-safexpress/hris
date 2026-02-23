<?php

namespace Database\Seeders\core;
use App\Models\Core\Menu;
use Illuminate\Database\Seeder;

class CoreMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $filePath = database_path(). '/seeders/core/seedfiles/Menu.json';
        $str = file_get_contents($filePath);
        $json = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $str), true );
        foreach ($json as $menuData) {
            // Idempotent insert/update to avoid duplicate primary key errors
            Menu::updateOrCreate(
                ['id' => $menuData['id']],
                [
                    'uuid' => $menuData['uuid'],
                    'app_id' => $menuData['app_id'],
                    'name' => $menuData['name'],
                    'parent_id' => $menuData['parent_id'],
                    'route' => $menuData['route'],
                    'icon' => $menuData['icon'],
                    'sort_order' => $menuData['sort_order'],
                    'is_active' => $menuData['is_active'],
                    'created_by' => 1,
                ]
            );
        }
    }

    private function importMenuFromCSV() {
        $filename = database_path('seeders/core/seedfiles/CoreUserMenus.csv');
        if( ! $filehandle = fopen($filename, 'r') ) return [];

        $tablecolumns = array('user_id','menu_id','is_manage','is_active');
        $menus = [];

        while( $rowvalues = fgetcsv($filehandle) ) {
            if ($rowvalues[0] == 'name') continue;
            if (count($rowvalues) == count($tablecolumns)) {
                $menus[] = array_combine($tablecolumns,$rowvalues);
            }
        }
        fclose($filehandle);
        return $menus;
    }

}
