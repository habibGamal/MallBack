<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->tinyText('governorate');
            $table->boolean('can_return');
            $table->string('business_type');
            // 0 => home , 1 => shop
            $table->boolean('work_from');
            $table->boolean('active')->default(false);
            $table->string('holidays',56);
            // work hours hh:mm am|pm [from->to] = (8*2) = 16 char
            $table->string('work_hours',16);
            $table->unsignedBigInteger('admin_id');
            $table->timestamps();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stores');
    }
}
