<?php

namespace App\Provider;

class OpenWeatherMapProvider extends AbstractProvider implements WeatherProviderInterface
{
    const URL = 'http://api.openweathermap.org/data/2.5/weather?q=%s&appid=8a96c9742f0ef23f67ea107c07150d85';
    const CACHE_FILE = 'OpenWatherMap.json';

    private function fetch($city)
    {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, sprintf(self::URL, $city));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $content = curl_exec($ch);
        curl_close($ch);

        if ($content) {
            $this->saveCache(self::CACHE_FILE, $content);
        } else {
            $content = $this->loadFromCache(self::CACHE_FILE);

            if (!$content) {
                throw new \ErrorException("Unable to fetch data");
            }
        }

        return $content;
    }

    public function getCurrentTemperature($city)
    {
        return round(json_decode($this->fetch($city), true)['main']['temp'] / 32);
    }
}