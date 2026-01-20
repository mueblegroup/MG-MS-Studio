<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropClassCardUsagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('class_card_usages');
    }

    /**
     * Reverse the migrations.
     *  
     * @return void
     */
    public function down()
    {
        Schema::create('class_card_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_class_card_id');
            $table->unsignedBigInteger('class_session_id');
            $table->timestamp('used_at')->useCurrent();
            $table->timestamps();
        });
    }
}
