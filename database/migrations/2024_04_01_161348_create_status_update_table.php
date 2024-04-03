<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusUpdateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('status_update', function (Blueprint $table) {
            $table->unsignedBigInteger('exp_id')->primary(); // exp_id as primary key
            $table->unsignedBigInteger('emp_id');
            $table->foreign('emp_id')->references('user_id')->on('users'); // assuming 'users' is the name of the users table
            $table->string('note')->nullable();
            $table->string('status')->required();
            $table->string('image')->nullable();
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
        Schema::dropIfExists('status_update');
    }
}
