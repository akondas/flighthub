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
                    'id' => JsonSchema::uuid(),
                    'number' => JsonSchema::string(['pattern' => '^[A-Z0-9]{3,}$'])
                ]
            )
        );
    }
}
