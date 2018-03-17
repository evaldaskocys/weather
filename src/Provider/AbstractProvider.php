<?php

namespace App\Provider;

abstract class AbstractProvider implements WeatherProviderInterface
{
    protected function saveCache($cacheFile, $content)
    {
        file_put_contents($cacheFile, $content);
    }

    protected function loadFromCache($cacheFile)
    {
        return file_get_contents($cacheFile);
    }
}
