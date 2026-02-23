<?php

namespace Database\Seeders\core;

use App\Models\Core\User;
use Illuminate\Database\Seeder;

class CoreUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $filePath = database_path() . '/seeders/core/seedfiles/CoreUser.json';
        $str = file_get_contents($filePath);
        $json = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $str), true);
        foreach ($json as $value) {
            // Idempotent insert/update to avoid duplicate primary key errors
            User::updateOrCreate(
                ['id' => $value['id']],
                [
                    'branch_id' => $value['branch_id'],
                    'name' => $value['name'],
                    'email' => $value['email'],
                    'email_verified_at' => $value['emailVerifiedAt'],
                    // Will be hashed via User model mutator
                    'password' => '123p@ss',
                    'user_type' => $value['userType'],
                    'member_role' => $value['member_role'],
                    'google_id' => $value['google_id'],
                    'first_name' => $value['firstName'],
                    'last_name' => $value['lastName'],
                    'photo' => $value['photo'],
                    'language' => $value['language'],
                    'status' => $value['status'],
                    'sitehead_user_id' => $value['sitehead_user_id'],
                    'created_by' => 0,
                ]
            );
        }
    }
}
