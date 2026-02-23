<?php

namespace Database\Seeders\core;
use App\Models\Core\Role;
use Illuminate\Database\Seeder;

class CoreRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $filePath = database_path(). '/seeders/core/seedfiles/CoreRole.json';
        $str = file_get_contents($filePath);
        $json = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $str), true );
        foreach ($json as $value) {
            // Make seeder idempotent
            Role::updateOrCreate(
                ['id' => $value['id']],
                [
                    'code' => $value['code'] ?? null,
                    'app_id' => $value['app_id'],
                    'name' => $value['name'],
                    'description' => $value['description'],
                    'is_active' => $value['is_active'],
                    'created_by' => 0,
                ]
            );
        }
    }

}
