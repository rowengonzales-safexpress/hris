<?php

namespace Database\Seeders\task;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TaskTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $types = [
            ['uuid' => (string) Str::uuid(), 'code' => 'LEAVE', 'name' => 'LEAVE', 'created_at' => $now],
            ['uuid' => (string) Str::uuid(), 'code' => 'ON_BUSINESS_TRAVEL', 'name' => 'ON BUSINESS TRAVEL', 'created_at' => $now],
            ['uuid' => (string) Str::uuid(), 'code' => 'SITE_VISIT', 'name' => 'SITE VISIT', 'created_at' => $now],
            ['uuid' => (string) Str::uuid(), 'code' => 'COA', 'name' => 'COA', 'created_at' => $now],
            ['uuid' => (string) Str::uuid(), 'code' => 'WORK_FROM_HOME', 'name' => 'WORK FROM HOME', 'created_at' => $now],
            ['uuid' => (string) Str::uuid(), 'code' => 'HOLIDAY', 'name' => 'HOLIDAY', 'created_at' => $now],
        ];

        DB::table('task_type')->insert($types);
    }
}
