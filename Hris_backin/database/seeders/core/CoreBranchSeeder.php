<?php
namespace Database\Seeders\core;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CoreBranchSeeder extends Seeder
{
    public function run()
    {
        $branches = [
            [
                'branch_code' => 'JSU',
                'branch_name' => 'JSU Cebu',
                'status' => 'A',
                'uuid' => Str::uuid(),
            ],
            [
                'branch_code' => 'SYSU',
                'branch_name' => 'SYSU Cebu',
                'status' => 'A',
                'uuid' => Str::uuid(),
            ],
            [
                'branch_code' => 'SLIFINANCE',
                'branch_name' => 'Finance HO',
                'status' => 'A',
                'uuid' => Str::uuid(),
            ],
        ];

        foreach ($branches as $branch) {
            // Idempotent upsert based on unique branch_name to avoid duplicate key errors
            DB::table('core_branch')->updateOrInsert(
                ['branch_name' => $branch['branch_name']],
                [
                    'uuid' => $branch['uuid'],
                    'branch_code' => $branch['branch_code'],
                    'status' => $branch['status'],
                    'fulladdress' => null,
                    'immediate_head' => null,
                    'updated_at' => now(),
                ]
            );
        }
    }
}
