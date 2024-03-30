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
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('name', 50)->nullable();
            $table->string('email', 60)->nullable();
            $table->string('user_phone', 10)->nullable();
            $table->string('account_no', 500)->nullable();
            $table->string('khalti_id', 500)->nullable();
            $table->tinyInteger('login_status')->default(0)->comment('0 = user not login, 1 = logged in');
            $table->tinyInteger('user_role')->default(1)->comment('1-Admin, 2-Order Team, 3 - Customer Support, 4- Customer Target, 5-Data Entry, 6- Customer Target + Data Entry, 7-Social Media, 8-social media + order processing, 9- Warehouse Incharge, 10-Scanner');
            $table->tinyInteger('super_user')->unsigned()->default(0)->comment('1-Yes, 0-No, 2-Finance');
            $table->timestamp('last_login')->useCurrent()->useCurrentOnUpdate();
            $table->timestamps();

            $table->index('email');
            $table->index('user_phone');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
