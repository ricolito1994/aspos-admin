<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    const TABLE_NAME = 'users';

    public function run(): void
    {
        $data = array(
            array(
                'name' => 'Neil Raymond Baylon',
                'email' => 'neil.baylon@4bhardware.com',
                'phone' => '09507199111',
                'username' => '4badminpos',
                'password' => Hash::make('4bhardwareadmin'),
                'designation' => 1, // 1 as admin
                'company_id' => 1,
                'is_owner' => 1,
                'selected_branch' => 1,
                'branch_id' => 1,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ),
            array(
                'name' => 'Paolo Baylon',
                'email' => 'paolo.baylon@4bhardware.com',
                'phone' => '09507199111',
                'username' => '4badminpos1',
                'password' => Hash::make('4bhardwareadmin'),
                'designation' => 1, // 1 as admin
                'company_id' => 1,
                'is_owner' => 1,
                'selected_branch' => 1,
                'branch_id' => 2,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ),
        );

        foreach ($data as $userData) {
            DB::table(self::TABLE_NAME)->insert($userData);
        }
    }
}
