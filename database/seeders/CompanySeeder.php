<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    const TABLE_NAME = 'company';

    public function run(): void
    {
        $data = array(
            array(
                'company_name' => 'Four B Hardware',
                'address' => 'Bago City Public Market, Bago City, Philippines',
                'owner_id' => 1,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ),
        );

        foreach ($data as $userData) {
            DB::table(self::TABLE_NAME)->insert($userData);
        }
    }
}
