<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class PopulateMonthlyDepositAmountInSavingsAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('UPDATE savings_accounts AS a JOIN savings_products AS p ON a.savings_product_id = p.id SET a.monthly_deposit_amount = p.monthly_deposit_amount');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('savings_accounts')->update(['monthly_deposit_amount' => 0]);
    }
}
