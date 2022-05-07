<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Schema;

use Tests\TestCase;

use DateTime;
use DateTimeZone;


class WeatherForecastInquiryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }


    public function testGetWeatherForecast(): void
    {
        $response = $this->get('/api/get-weather-forecast');
        $response->assertStatus(200);
    }


    public function testGetWeatherForecastEmptyInput(): void
    {
        $response = $this->getJson('/api/get-weather-forecast');
        $response->assertStatus(200)
            ->assertJson([
                'Error' => 'Format Error',
                'Date' => ''
            ]);
    }


    public function testGetWeatherForecastFormatError(): void
    {
        $date = '1234567890';
        $response = $this->getJson('/api/get-weather-forecast?date=' . $date);
        $response->assertStatus(200)
            ->assertJson([
                'Error' => 'Format Error',
                'Date' => $date
            ]);
    }


    public function testGetWeatherForecastDataIncorrectDate(): void
    {
        $date = '2022-99-99 03:00:00';
        $response = $this->getJson('/api/get-weather-forecast?date=' . $date);
        $response->assertStatus(200)
            ->assertJson([
                'Error' => 'Incorrect Date',
                'Date' => $date
            ]);
    }


    public function testGetWeatherForecastDataWillNotBeFound(): void
    {
        $date = '9999-05-02 03:00:00';
        $response = $this->getJson('/api/get-weather-forecast?date=' . $date);
        $response->assertStatus(200)
            ->assertJson([
                'Response' => 'No weather data was found for the specified date.',
                'Date' => $date
            ]);
    }


    public function testGetWeatherForecastDataWillBeFound(): void
    {
        $now = new DateTime("now", new DateTimeZone('UTC'));
        $tomorrow = $now->modify("+1 day");
        $date = $tomorrow->format("Y-m-d H:i:s");

        $response = $this->getJson('/api/get-weather-forecast?date=' . $date);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'dt',
                'dt_txt',
                'new_york_main',
                'new_york_description',
                'london_main',
                'london_description',
                'paris_main',
                'paris_description',
                'berlin_main',
                'berlin_description',
                'tokyo_main',
                'tokyo_description',
                'created_at',
                'updated_at',
            ]);
    }


    public function testWeatherDataTableColumns()
    {
        $this->assertTrue(
            Schema::hasColumns('weather_data', [
                'id',
                'dt',
                'dt_txt',
                'new_york_main',
                'new_york_description',
                'london_main',
                'london_description',
                'paris_main',
                'paris_description',
                'berlin_main',
                'berlin_description',
                'tokyo_main',
                'tokyo_description',
                'created_at',
                'updated_at',
            ]), 1
        );
    }


    public function testArtisanSheduleRunCommand()
    {
        $this->artisan('schedule:run')->assertExitCode(0);
    }


    public function testArtisanMigrateFreshCommand()
    {
        $this->artisan('migrate:fresh')->assertExitCode(0);
    }
}
