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
            ->identifiedBy(Payload::FLIGHT_ID)
            ->handle([Flight::class, 'add'])
            ->recordThat(Event::FLIGHT_ADDED)
            ->apply([Flight::class, 'whenFlightAdded'])
        ;

        $eventMachine->process(Command::RESERVE_TICKET)
            ->withExisting(self::FLIGHT)
            ->handle([Flight::class, 'reserveTicket'])
            ->recordThat(Event::TICKET_RESERVED)
            ->apply([Flight::class, 'whenTicketReserved'])
        ;
    }
}
