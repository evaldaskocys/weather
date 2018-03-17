<?php

namespace App\Provider;

interface WeatherProviderInterface
{
    public function getCurrentTemperature($city);
}
