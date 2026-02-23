<?php

namespace Database\Seeders\core;


use App\Models\Core\CoreAppUser;
use Illuminate\Database\Seeder;

class CoreAppUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $filePath = database_path().'/seeders/core/seedfiles/CoreAppUser.json';
        $str = file_get_contents($filePath);
        $json = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $str), true );
        foreach ($json as $value) {
            // Use updateOrCreate to make seeder idempotent and avoid duplicate primary key errors
            CoreAppUser::updateOrCreate(
                ['id' => $value['id']],
                [
                    'app_id' => $value['app_id'],
                    'user_id' => $value['user_id'],
                    'is_active' => $value['is_active'],
                    'created_by' => 1,
                ]
            );
        }
    }

}
