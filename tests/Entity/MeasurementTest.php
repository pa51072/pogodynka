<?php

namespace App\Tests\Entity;

use App\Entity\Measurement;
use PHPUnit\Framework\TestCase;

class MeasurementTest extends TestCase
{
    /**
     * @dataProvider dataGetFahrenheit
     */
    public function testGetFahrenheit($celsius, $expectedFahrenheit): void
    {
        // Tworzymy instancję Measurement
        $measurement = new Measurement();

        // Ustawiamy temperaturę w Celsjuszach
        $measurement->setTemperature($celsius);

        // Sprawdzamy czy wynik w Fahrenheicie jest zgodny z oczekiwanym
        $this->assertEquals($expectedFahrenheit, $measurement->getFahrenheit(), "{$celsius}°C should be {$expectedFahrenheit}°F");
    }

    public function dataGetFahrenheit(): array
    {
        return [
            ['0', 32],           // 0°C -> 32°F
            ['-100', -148],      // -100°C -> -148°F
            ['100', 212],        // 100°C -> 212°F
            ['0.5', 32.9],       // 0.5°C -> 32.9°F
            ['-0.5', 31.1],      // -0.5°C -> 31.1°F
            ['25', 77],          // 25°C -> 77°F
            ['-40', -40],        // -40°C -> -40°F (eksperymentalny punkt, gdzie °C = °F)
            ['37.5', 99.5],      // 37.5°C -> 99.5°F
            ['20', 68],          // 20°C -> 68°F
            ['-5.5', 22.1],      // -5.5°C -> 22.1°F
        ];
    }
}
