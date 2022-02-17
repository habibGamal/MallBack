<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('s_user_id')->nullable();
            $table->foreign('s_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('r_user_id')->nullable();
            $table->foreign('r_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('s_branch_id')->nullable();
            $table->foreign('s_branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->unsignedBigInteger('r_branch_id')->nullable();
            $table->foreign('r_branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->text('message');
            $table->boolean('seen')->default(false);
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
        Schema::dropIfExists('notifications');
    }
}
