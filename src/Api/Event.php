<?php

declare(strict_types=1);

namespace FlightHub\Api;

use Prooph\EventMachine\EventMachine;
use Prooph\EventMachine\EventMachineDescription;
use Prooph\EventMachine\JsonSchema\JsonSchema;

class Event implements EventMachineDescription
{
    public const FLIGHT_ADDED = 'FlightAdded';

    public static function describe(EventMachine $eventMachine): void
    {
        $eventMachine->registerEvent(
            self::FLIGHT_ADDED,
            JsonSchema::object(
                [
                    Payload::ID => Schema::id(),
                    Payload::NUMBER => Schema::flightNumber()
                ]
            )
        );
    }
}
