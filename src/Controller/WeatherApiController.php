<?php

namespace App\Controller;

use App\Service\WeatherUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

class WeatherApiController extends AbstractController
{
    public function __construct(
        private WeatherUtil $weatherUtil
    ) {}

    #[Route('/api/v1/weather', name: 'app_weather_api')]
    public function index(
        #[MapQueryParameter('country')] string $country,
        #[MapQueryParameter('city')] string $city,
        #[MapQueryParameter('format')] ?string $format = 'json',
        #[MapQueryParameter('twig')] bool $twig = false,
    ): JsonResponse|Response {
        // Pobranie prognozy pogody
        $measurements = $this->weatherUtil->getWeatherForCountryAndCity($country, $city);

        // Obsługa formatu CSV
        if ($format === 'csv') {
            if ($twig) {
                return $this->render('weather_api/index.csv.twig', [
                    'city' => $city,
                    'country' => $country,
                    'measurements' => $measurements,
                ]);
            }

            // Generowanie danych CSV z temperaturą w Celsjuszach i Fahrenheitach
            $csvData = "city,country,date,celsius,fahrenheit\n" . implode(
                "\n",
                array_map(fn($measurement) => sprintf(
                    '%s,%s,%s,%s,%s',
                    $city,
                    $country,
                    $measurement->getDate()->format('Y-m-d'),
                    $measurement->getTemperature(),
                    $measurement->getFahrenheit()
                ), $measurements)
            );

            return new Response($csvData, 200, ['Content-Type' => 'text/csv']);
        }

        // Obsługa formatu JSON
        if ($twig) {
            return $this->render('weather_api/index.json.twig', [
                'city' => $city,
                'country' => $country,
                'measurements' => $measurements,
            ]);
        }

        // Generowanie odpowiedzi JSON z temperaturą w Celsjuszach i Fahrenheitach
        return $this->json([
            'city' => $city,
            'country' => $country,
            'measurements' => array_map(fn($measurement) => [
                'date' => $measurement->getDate()->format('Y-m-d'),
                'celsius' => $measurement->getTemperature(),
                'fahrenheit' => $measurement->getFahrenheit(),
            ], $measurements),
        ]);
    }
}
