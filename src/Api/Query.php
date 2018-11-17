<?php

declare(strict_types=1);

namespace FlightHub\Api;

use FlightHub\Infrastructure\Finder\FlightFinder;
use FlightHub\Infrastructure\System\HealthCheckResolver;
use Prooph\EventMachine\EventMachine;
use Prooph\EventMachine\EventMachineDescription;
use Prooph\EventMachine\JsonSchema\JsonSchema;

class Query implements EventMachineDescription
{
    public const HEALTH_CHECK = 'HealthCheck';
    public const FLIGHT = 'Flight';
    public const FLIGHTS = 'Flights';

    public static function describe(EventMachine $eventMachine): void
    {
        $eventMachine->registerQuery(self::HEALTH_CHECK)
            ->resolveWith(HealthCheckResolver::class)
            ->setReturnType(Schema::healthCheck());

        $eventMachine->registerQuery(self::FLIGHT, JsonSchema::object([
            Payload::FLIGHT_ID => Schema::id(),
        ]))
            ->resolveWith(FlightFinder::class)
            ->setReturnType(Schema::flight());

        $eventMachine->registerQuery(self::FLIGHTS, JsonSchema::object(
                [],
                [Payload::NUMBER => JsonSchema::nullOr(Schema::flightNumberFilter())]
        ))
            ->resolveWith(FlightFinder::class)
            ->setReturnType(Schema::flightList());
    }
}
