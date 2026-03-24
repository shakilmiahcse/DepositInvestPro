<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMonthlyDepositAmountToSavingsAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('savings_accounts', function (Blueprint $table) {
            $table->decimal('monthly_deposit_amount', 10, 2)->default(0)->after('opening_balance');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('savings_accounts', function (Blueprint $table) {
            $table->dropColumn('monthly_deposit_amount');
        });
    }
}
