<?php

namespace App\Provider;

class ApixuProvider extends AbstractProvider implements WeatherProviderInterface
{
    const URL = 'https://api.apixu.com/v1/current.json?key=e4f06cf645194ff88b9190500181603&q=';
    const CACHE_FILE = 'apixu.json';

    private function fetch($city)
    {
        $content = file_get_contents(self::URL . $city);

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
        return json_decode($this->fetch($city), true)['current']['temp_c'];
    }
}