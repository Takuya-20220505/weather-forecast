<?php

namespace App\Listeners;

use App\Events\WeatherForecastInquiryEvent;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class WeatherForecastInquiryNotification
{
    private const openweathermap_url = 'https://api.openweathermap.org/data/2.5/forecast';
    private const openweathermap_appid = '199b75177d487aaadd4e634813b3b7ce';

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\WeatherForecastInquiryEvent  $event
     * @return void
     */
    public function handle(WeatherForecastInquiryEvent $event)
    {
        $weather_NewYork = $this->getWeatherFromOpenWeatherMap('40.730610', '-73.935242', 'new_york');
        $weather_London = $this->getWeatherFromOpenWeatherMap('51.509865', '-0.118092', 'london');
        $weather_Paris = $this->getWeatherFromOpenWeatherMap('48.864716', '2.349014', 'paris');
        $weather_Berlin = $this->getWeatherFromOpenWeatherMap('52.520008', '13.404954', 'berlin');
        $weather_Tokyo = $this->getWeatherFromOpenWeatherMap('35.652832', '139.839478', 'tokyo');

        $dt_array = array_merge( array_keys($weather_NewYork), array_keys($weather_London), array_keys($weather_Paris),
                                 array_keys($weather_Berlin), array_keys($weather_Tokyo));

        $weather_dt_list = [];
        foreach ($dt_array as $dt) {
            $dt_NewYork = $weather_NewYork[$dt];
            $dt_London = $weather_London[$dt];
            $dt_Paris = $weather_Paris[$dt];
            $dt_Berlin = $weather_Berlin[$dt];
            $dt_Tokyo = $weather_Tokyo[$dt];
            $weather_dt_list[] = $dt_NewYork + $dt_London + $dt_Paris + $dt_Berlin + $dt_Tokyo;
        }

        $columns_to_be_updated = ['new_york_main', 'new_york_description', 'london_main', 'london_description',
                                  'paris_main', 'paris_description', 'berlin_main', 'berlin_description',
                                  'tokyo_main', 'tokyo_description'];

        DB::table('weather_data')->upsert($weather_dt_list, ['dt'], $columns_to_be_updated);
    }

    private function getWeatherFromOpenWeatherMap($lat, $lon, $city)
    {
        $openweathermap_response = Http::get(self::openweathermap_url, [
            'lat' => $lat,
            'lon' => $lon,
            'appid' => self::openweathermap_appid,
        ]);
        $openweathermap_json = $openweathermap_response->json();

        $weather_list = $openweathermap_json['list'];
        $weather_dt_list = [];

        foreach ($weather_list as $weather_item) {
            $dt = $weather_item['dt'];
            $weather_dt_item['dt'] = $weather_item['dt'];
            $weather_dt_item['dt_txt'] = $weather_item['dt_txt'];
            $weather_contents = $weather_item['weather'];
            if (!empty($weather_contents) && !empty($weather_contents[0])) {
                if (isset($weather_contents[0]['main'])) {
                    $weather_dt_item[$city . '_main'] = $weather_contents[0]['main'];
                }
                if (isset($weather_contents[0]['description'])) {
                    $weather_dt_item[$city . '_description'] = $weather_contents[0]['description'];
                }
            }
            $weather_dt_list[$dt] = $weather_dt_item;
        }
        return $weather_dt_list;
    }
}
