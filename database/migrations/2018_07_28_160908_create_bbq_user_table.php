<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBbqUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bbq_user', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('bbq_id');
            $table->unsignedInteger('user_id');
            $table->dateTime('reserved_from');
            $table->dateTime('reserved_to');
            $table->timestamps();

            $table->foreign('bbq_id')->references('id')->on('bbqs')
            ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')
            ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bbq_user');
    }
}
