<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionsHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions_history', function (Blueprint $table) {
            $table->string('id');
            $table->string('subscription_id');
            $table->timestamps();
            $table->string('product_id');
            $table->string('transaction_id');
            $table->string('environment');
            $table->integer('start_date');
            $table->integer('end_date');
            $table->string('type');
            $table->integer('count');
            $table->text('receipt');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions_history');
    }
}