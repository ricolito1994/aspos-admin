<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    const TABLE_NAME = 'branch';

    public function run(): void
    {
        $data = array(
            array(
                'branch_name' => 'Four B Hardware',
                'branch_address' => 'Bago City Public Market, Bago City, Philippines',
                'branch_code' => '4B',
                'owner_id' => 1,
                'company_id' => 1,
                'branch_head' => 1,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ),
            array(
                'branch_name' => 'Alongside Hardware',
                'branch_address' => 'General Luna St., Bago City, Philippines',
                'branch_code' => 'AH',
                'owner_id' => 1,
                'company_id' => 1,
                'branch_head' => 2,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ),
        );

        foreach ($data as $userData) {
            DB::table(self::TABLE_NAME)->insert($userData);
        }
    }
}
