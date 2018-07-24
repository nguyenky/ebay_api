<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('SKU')->nullable();
            $table->string('Name')->nullable();
            $table->longText('Description')->nullable();
            $table->string('Category')->nullable();
            $table->string('Size')->nullable();
            $table->string('Color')->nullable();
            $table->float('Cost',10,2)->nullable();
            $table->float('Sell',10,2)->nullable();
            $table->float('RRP',10,2)->nullable();
            $table->integer('QTY')->nullable();
            $table->string('Image1')->nullable();
            $table->string('Image2')->nullable();
            $table->string('Image3')->nullable();
            $table->string('Image4')->nullable();
            $table->string('Image5')->nullable();
            $table->integer('Length')->nullable();
            $table->integer('Width')->nullable();
            $table->integer('Height')->nullable();
            $table->integer('UnitWeight')->nullable();
            $table->string('Origin')->nullable();
            $table->string('Construction')->nullable();
            $table->string('Material')->nullable();
            $table->string('Pileheight')->nullable();
            $table->string('offerID')->nullable();
            $table->string('listingID')->nullable();
            $table->float('listing_price',10,2)->nullable();
            $table->timestamp('ebayupdated_at')->nullable();
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
        Schema::dropIfExists('products');
    }
}
