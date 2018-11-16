<?php

declare(strict_types=1);

namespace FlightHub\Api;

use FlightHub\Domain\Flight;
use Prooph\EventMachine\EventMachine;
use Prooph\EventMachine\EventMachineDescription;

class Aggregate implements EventMachineDescription
{
    public const FLIGHT = 'Flight';

    public static function describe(EventMachine $eventMachine): void
    {
        $eventMachine->process(Command::ADD_FLIGHT)
            ->withNew(self::FLIGHT)
            ->identifiedBy('id')
            ->handle([Flight::class, 'add'])
            ->recordThat(Event::FLIGHT_ADDED)
            ->apply([Flight::class, 'whenFlightAdded'])
        ;
    }
}
