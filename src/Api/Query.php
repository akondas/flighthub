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
        //Default query: can be used to check if service is up and running
        $eventMachine->registerQuery(self::HEALTH_CHECK) //<-- Payload schema is optional for queries
            ->resolveWith(HealthCheckResolver::class) //<-- Service id (usually FQCN) to get resolver from DI container
            ->setReturnType(Schema::healthCheck()); //<-- Type returned by resolver

        $eventMachine->registerQuery(self::FLIGHT, JsonSchema::object([
            'id' => JsonSchema::uuid(),
        ]))
            ->resolveWith(FlightFinder::class)
            ->setReturnType(JsonSchema::typeRef(Aggregate::FLIGHT));

        $eventMachine->registerQuery(self::FLIGHTS, JsonSchema::object(
                [],
                ['number' => JsonSchema::nullOr(JsonSchema::string()->withMinLength(1))]
        ))
            ->resolveWith(FlightFinder::class)
            ->setReturnType(JsonSchema::array(
                JsonSchema::typeRef(Aggregate::FLIGHT)
            ));
    }
}
