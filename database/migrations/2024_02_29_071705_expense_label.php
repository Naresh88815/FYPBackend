<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expense_label', function (Blueprint $table) {
            $table->id('label_id');
            $table->string('label_name', 500)->nullable();
            $table->unsignedBigInteger('emp_id')->nullable(); // Use unsignedBigInteger for foreign keys
            $table->tinyInteger('status')->default(1)->comment('1 = Active, 0 = Inactive');
            $table->timestamps();
        
            // Add foreign key constraint
            $table->foreign('emp_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expense_label');
    }
};
