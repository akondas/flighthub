<?php

declare(strict_types=1);

namespace FlightHub\Api;

use FlightHub\Domain\Flight;
use FlightHub\Infrastructure\Handler\FlightHandler;
use Prooph\EventMachine\EventMachine;
use Prooph\EventMachine\EventMachineDescription;

final class Handler implements EventMachineDescription
{
    public static function describe(EventMachine $eventMachine): void
    {
        $eventMachine->process(Command::ADD_FLIGHT)
            ->withNew(Aggregate::FLIGHT)
            ->identifiedBy(Payload::FLIGHT_ID)
            ->handle([FlightHandler::class, 'add'])
            ->recordThat(Event::FLIGHT_ADDED)
            ->apply([Flight::class, 'whenFlightAdded']);

        $eventMachine->process(Command::RESERVE_TICKET)
            ->withExisting(Aggregate::FLIGHT)
            ->handle([FlightHandler::class, 'reserveTicket'])
            ->recordThat(Event::TICKET_RESERVED)
            ->apply([FlightHandler::class, 'apply']);

        $eventMachine->process(Command::BLOCK_SEAT)
            ->withExisting(Aggregate::FLIGHT)
            ->handle([FlightHandler::class, 'blockSeat'])
            ->recordThat(Event::SEAT_BLOCKED)
            ->apply([FlightHandler::class, 'apply']);
    }
}
