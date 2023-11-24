<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    const TABLE_NAME = 'users';

    public function run(): void
    {
        $data = array(
            array(
                'name' => 'CASHIER 1',
                'email' => 'cashier.1@4bhardware.com',
                'phone' => '09507199111',
                'username' => '4bcashier1',
                'password' => Hash::make('4bcashier01'),
                'designation' => 3,
                'company_id' => 1,
                'is_owner' => 0,
                'selected_branch' => 1,
                'branch_id' => 1,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ),
            array(
                'name' => 'CASHIER 2',
                'email' => 'cashier.2@4bhardware.com',
                'phone' => '09507199111',
                'username' => '4bcashier2',
                'password' => Hash::make('4bcashier02'),
                'designation' => 3,
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
