<?php

declare(strict_types=1);

namespace FlightHub\Application;

use Prooph\EventMachine\EventMachine;
use Prooph\EventMachine\EventMachineDescription;
use Prooph\EventMachine\Runtime\Oop\FlavourHint;

final class FlightDescription implements EventMachineDescription
{
    public static function describe(EventMachine $eventMachine): void
    {
        $eventMachine->process(Command::ADD_FLIGHT)
            ->withNew(Aggregate::FLIGHT)
            ->identifiedBy(Payload::FLIGHT_ID)
            ->handle([FlavourHint::class, 'useAggregate'])
            ->recordThat(Event::FLIGHT_ADDED)
            ->apply([FlavourHint::class, 'useAggregate']);

        $eventMachine->process(Command::RESERVE_TICKET)
            ->withExisting(Aggregate::FLIGHT)
            ->handle([FlavourHint::class, 'useAggregate'])
            ->recordThat(Event::TICKET_RESERVED)
            ->apply([FlavourHint::class, 'useAggregate']);

        $eventMachine->process(Command::BLOCK_SEAT)
            ->withExisting(Aggregate::FLIGHT)
            ->handle([FlavourHint::class, 'useAggregate'])
            ->recordThat(Event::SEAT_BLOCKED)
            ->apply([FlavourHint::class, 'useAggregate']);
    }
}
