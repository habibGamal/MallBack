<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->json('pictures');
            $table->string('name');
            $table->unsignedMediumInteger('price');
            $table->unsignedMediumInteger('offer_price')->nullable();
            // 0 => Out of stock
            // 1 => In stick
            // 2 => Upcomming
            $table->enum('stock',['0','1','2']);
            $table->boolean('returnable');
            // => optional
            $table->text('description')->nullable();
            $table->json('specifications')->nullable();
            $table->tinyText('brand')->nullable();
            $table->tinyText('warranty')->nullable();
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
