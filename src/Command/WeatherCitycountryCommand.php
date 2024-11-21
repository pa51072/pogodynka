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
    name: 'weather:citycountry',  // Nazwa komendy
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

    protected function configure(): void
    {
        $this
            ->addArgument('countryCode', InputArgument::REQUIRED, 'The country code (e.g., "PL")')
            ->addArgument('city', InputArgument::REQUIRED, 'The name of the city (e.g., "Police")');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $countryCode = $input->getArgument('countryCode');
        $city = $input->getArgument('city');

        // Find the location based on the country code and city name
        $location = $this->locationRepository->findOneBy(['country' => $countryCode, 'city' => $city]);

        if (!$location) {
            $output->writeln('<error>Location not found.</error>');
            return Command::FAILURE;
        }

        // Fetch weather data for the location
        $measurements = $this->weatherUtil->getWeatherForLocation($location);

        if (empty($measurements)) {
            $output->writeln('<info>No weather data available for this location.</info>');
            return Command::SUCCESS;
        }

        // Display only temperature and date
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
