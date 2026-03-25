<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profit_distribution_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profit_distribution_id')->constrained('profit_distributions')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('savings_accounts')->cascadeOnDelete();
            $table->decimal('deposit_amount', 15, 2);
            $table->decimal('profit_amount', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profit_distribution_details');
    }
};
