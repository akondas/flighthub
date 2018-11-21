<?php

declare(strict_types=1);

namespace FlightHub\Application\Description;

use FlightHub\Infrastructure\Finder\FlightFinder;
use FlightHub\Infrastructure\System\HealthCheckResolver;
use Prooph\EventMachine\EventMachine;
use Prooph\EventMachine\EventMachineDescription;
use Prooph\EventMachine\JsonSchema\JsonSchema;
use Prooph\EventMachine\Persistence\Stream;

final class MessageDescription implements EventMachineDescription
{
    public static function describe(EventMachine $eventMachine): void
    {
        // Command
        $eventMachine->registerCommand(Command::ADD_FLIGHT, JsonSchema::object([
            Payload::FLIGHT_ID => Schema::id(),
            Payload::NUMBER => Schema::flightNumber()
        ]))->registerCommand(Command::RESERVE_TICKET, JsonSchema::object([
            Payload::RESERVATION_ID => Schema::id(),
            Payload::USER_ID => Schema::id(),
            Payload::FLIGHT_ID => Schema::id(),
            Payload::SEAT => Schema::seat()
        ]))->registerCommand(Command::BLOCK_SEAT, JsonSchema::object([
            Payload::FLIGHT_ID => Schema::id(),
            Payload::SEAT => Schema::seat(),
            Payload::VERSION => Schema::version()
        ]));

        // Event
        $eventMachine->registerEvent(Event::FLIGHT_ADDED, JsonSchema::object([
            Payload::FLIGHT_ID => Schema::id(),
            Payload::NUMBER => Schema::flightNumber()
        ]))->registerEvent(Event::TICKET_RESERVED, JsonSchema::object([
            Payload::RESERVATION_ID => Schema::id(),
            Payload::USER_ID => Schema::id(),
            Payload::FLIGHT_ID => Schema::id(),
            Payload::SEAT => Schema::seat()
        ]))->registerEvent(Event::SEAT_BLOCKED, JsonSchema::object([
            Payload::FLIGHT_ID => Schema::id(),
            Payload::SEAT => Schema::seat()
        ]));

        // Projection
        $eventMachine->watch(Stream::ofWriteModel())
            ->withAggregateProjection(Aggregate::FLIGHT);

        // Query
        $eventMachine->registerType(Query::HEALTH_CHECK, JsonSchema::object([
            'system' => JsonSchema::boolean()
        ]));
        $eventMachine->registerType(Query::FLIGHT, JsonSchema::object([
            Payload::FLIGHT_ID => Schema::id(),
            Payload::NUMBER => Schema::flightNumber()
        ]));

        $eventMachine->registerQuery(Query::HEALTH_CHECK)
            ->resolveWith(HealthCheckResolver::class)
            ->setReturnType(Schema::healthCheck());

        $eventMachine->registerQuery(Query::FLIGHT, JsonSchema::object([
            Payload::FLIGHT_ID => Schema::id(),
        ]))
            ->resolveWith(FlightFinder::class)
            ->setReturnType(Schema::flight());

        $eventMachine->registerQuery(Query::FLIGHTS, JsonSchema::object(
            [],
            [Payload::NUMBER => JsonSchema::nullOr(Schema::flightNumberFilter())]
        ))
            ->resolveWith(FlightFinder::class)
            ->setReturnType(Schema::flightList());
    }
}
