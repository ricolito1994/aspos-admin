<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TruncateTransactionsSeeder extends Seeder
{
    const TABLE_NAME_TRANSACTION = 'transactions';
    const TABLE_NAME_TRANSACTION_DETAILS = 'transaction_details';
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table(self::TABLE_NAME_TRANSACTION)->truncate();
        DB::table(self::TABLE_NAME_TRANSACTION_DETAILS)->truncate();
    }
}
