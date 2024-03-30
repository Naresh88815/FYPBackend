<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id('expense_id');
            $table->unsignedBigInteger('emp_id')->nullable();
            $table->unsignedBigInteger('label_id')->nullable();
            $table->integer('amount')->nullable();
            $table->unsignedBigInteger('head_id')->nullable();
            $table->string('note', 500)->nullable();
            $table->string('image', 500)->nullable();
            $table->string('payment_type', 50)->nullable();
            $table->integer('status')->default(1)->comment('Pending = 1, Approve = 2, Cancel = 3, Decline = 4, Payment_Transfer = 5');
            $table->timestamps();
            
            $table->foreign('emp_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('label_id')->references('label_id')->on('expense_label')->onDelete('cascade');
            $table->foreign('head_id')->references('head_id')->on('expense_heads')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expenses');
    }
};
