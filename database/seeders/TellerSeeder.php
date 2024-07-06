<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TellerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    const TABLE_NAME = 'users';

    public function run(): void
    {
        $data = array(
            array(
                'name' => 'TELLER 1',
                'email' => 'teller.1@4bhardware.com',
                'phone' => '09507199111',
                'username' => '4bteller1',
                'password' => Hash::make('4bteller01'),
                'designation' => 5,
                'company_id' => 1,
                'is_owner' => 0,
                'selected_branch' => 1,
                'branch_id' => 1,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ),
            array(
                'name' => 'TELLER 2',
                'email' => 'teller.2@4bhardware.com',
                'phone' => '09507199111',
                'username' => '4bteller2',
                'password' => Hash::make('4bteller02'),
                'designation' => 5,
                'company_id' => 1,
                'is_owner' => 0,
                'selected_branch' => 1,
                'branch_id' => 1,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ),
        );

        foreach ($data as $userData) {
            DB::table(self::TABLE_NAME)->insert($userData);
        }
    }
}
