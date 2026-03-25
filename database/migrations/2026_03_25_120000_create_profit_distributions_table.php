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
        Schema::create('profit_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained('investments')->cascadeOnDelete();
            $table->decimal('total_profit', 15, 2);
            $table->decimal('distributed_amount', 15, 2);
            $table->decimal('remaining_profit', 15, 2)->default(0);
            $table->dateTime('distribution_date');
            $table->timestamps();

            $table->unique('investment_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profit_distributions');
    }
};
