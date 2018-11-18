<?php

declare(strict_types=1);

namespace FlightHub\Application;

use FlightHub\Domain\Aggregate\Flight;
use Prooph\EventMachine\EventMachine;
use Prooph\EventMachine\EventMachineDescription;
use Prooph\EventMachine\Runtime\Oop\InterceptorHint;

final class FlightDescription implements EventMachineDescription
{
    public static function describe(EventMachine $eventMachine): void
    {
        $eventMachine->process(Command::ADD_FLIGHT)
            ->withNew(Aggregate::FLIGHT)
            ->identifiedBy(Payload::FLIGHT_ID)
            ->handle([InterceptorHint::class, 'useAggregate'])
            ->recordThat(Event::FLIGHT_ADDED)
            ->apply([InterceptorHint::class, 'useAggregate']);

        $eventMachine->process(Command::RESERVE_TICKET)
            ->withExisting(Aggregate::FLIGHT)
            ->handle([InterceptorHint::class, 'useAggregate'])
            ->recordThat(Event::TICKET_RESERVED)
            ->apply([InterceptorHint::class, 'useAggregate']);

        $eventMachine->process(Command::BLOCK_SEAT)
            ->withExisting(Aggregate::FLIGHT)
            ->handle([InterceptorHint::class, 'useAggregate'])
            ->recordThat(Event::SEAT_BLOCKED)
            ->apply([InterceptorHint::class, 'useAggregate']);
    }
}
