<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('description');
            $table->date('start_date');
            $table->date('due_date');
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('creator_id');
            $table->unsignedInteger('finisher_id')->nullable();
            $table->timestamps();

            $table->foreign('creator_id')->references('id')->on('users');
            $table->foreign('finisher_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
