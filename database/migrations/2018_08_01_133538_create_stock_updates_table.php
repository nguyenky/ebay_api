<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockUpdatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_updates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sku')->nullable();
            $table->string('qty')->nullable();
            $table->string('incoming')->nullable();
            $table->string('due')->nullable();
            $table->string('discontinued')->nullable();
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
        Schema::dropIfExists('stock_updates');
    }
}
