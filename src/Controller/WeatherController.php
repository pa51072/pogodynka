<?php

namespace App\Controller;

use App\Entity\Location;
use App\Repository\MeasurementRepository;
use App\Repository\LocationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WeatherController extends AbstractController
{
    #[Route('/weather/{city}/{country}', name: 'app_weather', requirements: ['city' => '.+', 'country' => '[A-Z]{2}'])]
    public function city(string $city, string $country = null, LocationRepository $locationRepository, MeasurementRepository $repository): Response
    {

        // Znajdź lokalizację na podstawie nazwy miejscowości i kodu państwa
        if ($country) {
            $location = $locationRepository->findOneBy(['city' => $city, 'country' => $country]);
        } else {
            $location = $locationRepository->findOneBy(['city' => $city]);
        }

        // Sprawdź, czy lokalizacja została znaleziona
        if (!$location) {
            throw $this->createNotFoundException('Location not found');
        }

        // Pobierz pomiary dla znalezionej lokalizacji
        $measurements = $repository->findByLocation($location);

        return $this->render('weather/city.html.twig', [
            'location' => $location,
            'measurements' => $measurements,
        ]);
    }
}

