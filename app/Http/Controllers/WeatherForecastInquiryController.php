<?php

namespace App\Http\Controllers;

use App\Events\WeatherForecastInquiryEvent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use DateTime;


class WeatherForecastInquiryController extends Controller
{
    public function getWeatherForecast(Request $request) {

        $dt_txt = $request->date;
        if (!preg_match('/\A\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\z/', $dt_txt)) {
            $return_value = ['Error' => 'Format Error', 'Date' => $request->date];
            return response()->json($return_value);
        }

        $dt = 0;

        try {
            $dt_obj = new DateTime($dt_txt);
            $dt = $dt_obj->getTimestamp();
        } catch (\Exception $ex) {
            $dt = false;
        }

        if ($dt === false || $dt === 0) {
            $return_value = ['Error' => 'Incorrect Date', 'Date' => $request->date];
            return response()->json($return_value);
        }

        $dt_one_and_a_half_hours_ago = $dt - 5400;
        $dt_one_and_a_half_hours_later = $dt + 5400;

        $weather_data = DB::table('weather_data')
                        ->where('dt', '>=', $dt_one_and_a_half_hours_ago)
                        ->where('dt', '<', $dt_one_and_a_half_hours_later)->get();

        $weather_data_array = $weather_data->toArray();

        if (empty($weather_data_array)) {
            WeatherForecastInquiryEvent::dispatch();
            $weather_data = DB::table('weather_data')
                            ->where('dt', '>=', $dt_one_and_a_half_hours_ago)
                            ->where('dt', '<', $dt_one_and_a_half_hours_later)->get();
            $weather_data_array = $weather_data->toArray();
        }

        if (empty($weather_data_array)) {
            $return_value = ['Response' => 'No weather data was found for the specified date.', 'Date' => $request->date];
            return response()->json($return_value);
        }

        return response()->json($weather_data_array[0]);
    }
}
