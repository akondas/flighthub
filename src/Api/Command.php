<?php

declare(strict_types=1);

namespace FlightHub\Api;

use Prooph\EventMachine\EventMachine;
use Prooph\EventMachine\EventMachineDescription;
use Prooph\EventMachine\JsonSchema\JsonSchema;

class Command implements EventMachineDescription
{
    public const ADD_FLIGHT = 'AddFlight';

    public static function describe(EventMachine $eventMachine): void
    {
        $eventMachine->registerCommand(
            self::ADD_FLIGHT,
            JsonSchema::object(
                [
                    'id' => JsonSchema::uuid(),
                    'number' => JsonSchema::string(['pattern' => '^[A-Z0-9]{3,}$'])
                ]
            )
        );
    }
}
