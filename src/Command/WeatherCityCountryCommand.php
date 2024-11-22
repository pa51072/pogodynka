<?php

namespace App\Command;

use App\Service\WeatherUtil;
use App\Repository\LocationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'weather:city:country',  // Nazwa komendy
    description: 'Retrieve weather forecast for a city based on country code and city name',
)]
class WeatherCityCountryCommand extends Command
{
    private WeatherUtil $weatherUtil;
    private LocationRepository $locationRepository;

    public function __construct(WeatherUtil $weatherUtil, LocationRepository $locationRepository)
    {
        parent::__construct();
        $this->weatherUtil = $weatherUtil;
        $this->locationRepository = $locationRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $countryCode = $input->getArgument('countryCode');
        $city = $input->getArgument('city');

        $location = $this->locationRepository->findOneBy(['country' => $countryCode, 'city' => $city]);

        if (!$location) {
            $output->writeln('<error>Location not found.</error>');
            return Command::FAILURE;
        }

        $measurements = $this->weatherUtil->getWeatherForLocation($location);

        if (empty($measurements)) {
            $output->writeln('<info>No weather data available for this location.</info>');
            return Command::SUCCESS;
        }

        foreach ($measurements as $measurement) {
            $output->writeln(sprintf(
                "Location: %s\n" .
                "Date: %s\n" .
                "Temperature: %.1f°C\n",
                $measurement->getLocation()->getCity(),
                $measurement->getDate()->format('Y-m-d'),
                $measurement->getTemperature()
            ));
        }

        return Command::SUCCESS;
    }
}
