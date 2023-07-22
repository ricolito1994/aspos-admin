<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SupplierSeeder extends Seeder
{
    const TABLE_NAME = 'suppliers';
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            array(
                'supplier_name' => 'Alongside Hardware',
                'address' => 'Bago City',
                'contact_number' => '09507199111',
                'contact_person' => 'Niel Raymond Baylon',
                'email' => 'niel.baylon@4bhardware.com',
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ),
        );

        foreach ($data as $userData) {
            DB::table(self::TABLE_NAME)->insert($userData);
        }
    }
}
