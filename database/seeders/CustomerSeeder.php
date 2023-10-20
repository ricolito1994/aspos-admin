<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerSeeder extends Seeder
{
    const TABLE_NAME = 'customers';
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $data = array(
            array(
                'customer_name' => 'Ricolito Cantorias',
                'customer_code' => 'RAC01',
                'customer_type' => 1,
                'company_id' => 1,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ),
            array(
                'customer_name' => 'Neil Raymond Baylon',
                'customer_code' => 'NB01',
                'customer_type' => 1,
                'company_id' => 1,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ),
            array(
                'customer_name' => 'Triumph Hardware',
                'customer_code' => 'TH01',
                'customer_type' => 2,
                'company_id' => 1,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ),
            array(
                'customer_name' => 'Willtrade Marketing',
                'customer_code' => 'WM01',
                'customer_type' => 2,
                'company_id' => 1,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ),
        );

        foreach ($data as $customerData) {
            DB::table(self::TABLE_NAME)->insert($customerData);
        }
    }
}
