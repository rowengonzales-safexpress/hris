<?php

namespace Database\Seeders\core;
use App\Models\Core\CoreApp;
use Illuminate\Database\Seeder;

class CoreAppSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $filePath = database_path().'/seeders/core/seedfiles/CoreApp.json';
        $str = file_get_contents($filePath);
        $json = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $str), true );
        foreach ($json as $value) {
            // Use updateOrCreate to avoid duplicate primary key errors when reseeding
            CoreApp::updateOrCreate(
                ['id' => $value['id']],
                [
                    'code' => $value['code'],
                    'name' => $value['name'],
                    'description' => $value['description'],
                    'status' => $value['status'],
                    'status_message' => $value['status_message'],
                    'logo' => null,
                    'route' => $value['route'],
                    'created_by' => 0,
                ]
            );
        }
    }

}
