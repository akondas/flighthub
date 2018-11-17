<?php

declare(strict_types=1);

namespace FlightHub\Api;

use Prooph\EventMachine\EventMachine;
use Prooph\EventMachine\EventMachineDescription;
use Prooph\EventMachine\JsonSchema\JsonSchema;
use Prooph\EventMachine\JsonSchema\Type\ObjectType;
use FlightHub\Domain\Flight;

class Type implements EventMachineDescription
{
    public const HEALTH_CHECK = 'HealthCheck';
    public const RESERVATION = 'Reservation';

    private static function healthCheck(): ObjectType
    {
        return JsonSchema::object([
            'system' => JsonSchema::boolean()
        ]);
    }

    /**
     * @param EventMachine $eventMachine
     */
    public static function describe(EventMachine $eventMachine): void
    {
        //Register the HealthCheck type returned by @see \FlightHub\Api\Query::HEALTH_CHECK
        $eventMachine->registerType(self::HEALTH_CHECK, self::healthCheck());

        $eventMachine->registerType(Aggregate::FLIGHT, Flight\State::__schema());
        $eventMachine->registerType(self::RESERVATION, Flight\Reservation::__schema());
    }
}
