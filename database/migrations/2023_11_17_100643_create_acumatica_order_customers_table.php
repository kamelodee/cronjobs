<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcumaticaOrderCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acumatica_order_customers', function (Blueprint $table) {
            $table->id();
            $table->string('partner_id');
            $table->string('name');
            $table->string('phone_sanitized');
            $table->string('contact_address_complete');
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
        Schema::dropIfExists('acumatica_order_customers');
    }
}
