<?php

namespace App\Controller;

use App\Provider\WeatherProviderInterface;
use App\Provider\ApixuProvider;
use App\Provider\OpenWeatherMapProvider;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WeatherController extends Controller
{
    private $provider;

    public function __construct(WeatherProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @Route("/")
     * @param OpenWeatherMapProvider $provider
     * @return Response
     */
    public function weatherAction() : Response
    {
        $city = 'Vilnius';

        return new Response(
            sprintf(
                'Temperature: %s',
                $this->provider->getCurrentTemperature($city)
            )
        );
    }
}
