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
    name: 'weather:location',
    description: 'Display weather forecast for a specific location',
)]
class WeatherLocationCommand extends Command
{
    private WeatherUtil $weatherUtil;
    private LocationRepository $locationRepository;

    public function __construct(WeatherUtil $weatherUtil, LocationRepository $locationRepository)
    {
        $this->weatherUtil = $weatherUtil;
        $this->locationRepository = $locationRepository;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('locationId', InputArgument::REQUIRED, 'The ID of the location to get the weather forecast for');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Pobierz argument ID lokalizacji
        $locationId = (int) $input->getArgument('locationId');

        // Znajdź lokalizację na podstawie ID
        $location = $this->locationRepository->find($locationId);

        // Jeśli lokalizacja nie istnieje, wypisz komunikat o błędzie
        if (!$location) {
            $io->error(sprintf('Location with ID %d not found.', $locationId));
            return Command::FAILURE;
        }

        // Pobierz prognozę pogody za pomocą serwisu
        $measurements = $this->weatherUtil->getWeatherForLocation($location);

        // Wyświetl prognozę pogody
        $io->section(sprintf('Weather forecast for %s, %s:', $location->getCity(), $location->getCountry()));
        if (empty($measurements)) {
            $io->warning('No weather data available for this location.');
        } else {
            foreach ($measurements as $measurement) {
                $io->text(sprintf(
                    '- %s: %s°C',
                    $measurement->getDate()->format('Y-m-d'),
                    $measurement->getTemperature()
                ));
            }
        }

        return Command::SUCCESS;
    }
}
