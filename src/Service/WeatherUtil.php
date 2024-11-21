<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Location;
use App\Entity\Measurement;
use App\Repository\LocationRepository;
use App\Repository\MeasurementRepository;

class WeatherUtil
{

    public function __construct(
        private LocationRepository $locationRepository,
        private MeasurementRepository $measurementRepository
    ) {}

    /**
     * @return Measurement[]
     */
    public function getWeatherForLocation(Location $location): array
    {
        return $this->measurementRepository->findByLocation($location);
    }

    /**
     * @return Measurement[]
     */
    public function getWeatherForCountryAndCity(string $country, string $city): array
    {
        $location = $this->locationRepository->findOneBy(['country' => $country, 'city' => $city]);
        if ($location === null) {
            throw new \InvalidArgumentException('Location not found');
        }

        return $this->getWeatherForLocation($location);
    }
}
