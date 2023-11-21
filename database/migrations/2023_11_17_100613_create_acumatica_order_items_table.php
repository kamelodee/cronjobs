<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcumaticaOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acumatica_order_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('product_qty');
            $table->string('qty_to_deliver');
            $table->string('product_packaging_qty');
            $table->string('price_total');
            $table->string('product_template_id');
            $table->string('product_template_name');
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
        Schema::dropIfExists('acumatica_order_items');
    }
}
