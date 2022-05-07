<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WeatherData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weather_data', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->integer('dt')->unique();
            $table->string('dt_txt', 19)->unique();
            $table->string('new_york_main', 255)->nullable();
            $table->string('new_york_description', 255)->nullable();
            $table->string('london_main', 255)->nullable();
            $table->string('london_description', 255)->nullable();
            $table->string('paris_main', 255)->nullable();
            $table->string('paris_description', 255)->nullable();
            $table->string('berlin_main', 255)->nullable();
            $table->string('berlin_description', 255)->nullable();
            $table->string('tokyo_main', 255)->nullable();
            $table->string('tokyo_description', 255)->nullable();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
        });

        DB::unprepared('CREATE TRIGGER weather_data_updated_at AFTER UPDATE ON weather_data
            BEGIN
                UPDATE weather_data SET updated_at = CURRENT_TIMESTAMP WHERE rowid == NEW.rowid;
            END;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
