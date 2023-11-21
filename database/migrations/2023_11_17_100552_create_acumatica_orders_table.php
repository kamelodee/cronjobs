<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcumaticaOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acumatica_orders', function (Blueprint $table) {
            $table->id();
            $table->string('website_order_line');
            $table->string('delivery_status');
            $table->string('cart_quantity');
            $table->string('picking_policy');
            $table->string('partner_id');
            $table->string('date_order');
            $table->string('delivery_count');
            $table->string('type_name');
            $table->string('name');
            $table->string('display_name');
            $table->string('amount_total');
            $table->string('state');
            $table->string('order_line');
            $table->string('warehouse_id');
            $table->string('website_id');
            $table->string('warehouse');
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
        Schema::dropIfExists('acumatica_orders');
    }
}
