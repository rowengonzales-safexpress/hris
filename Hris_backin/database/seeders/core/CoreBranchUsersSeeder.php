<?php
namespace Database\Seeders\core;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CoreBranchUsersSeeder extends Seeder
{
    public function run()
    {
        $branches = DB::table('core_branch')->get();
        $users = DB::table('core_users')->get();

        $maxPairs = min(count($branches), count($users));

        for ($i = 0; $i < $maxPairs; $i++) {
            DB::table('core_branchusers')->insert([
                'uuid' => Str::uuid(),
                'branch_id' => $branches[$i]->id,
                'user_id' => $users[$i]->id,
            ]);
        }
    }
}
