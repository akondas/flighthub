<?php

declare(strict_types=1);

namespace FlightHub\Api;

use Prooph\EventMachine\EventMachine;
use Prooph\EventMachine\EventMachineDescription;
use Prooph\EventMachine\JsonSchema\JsonSchema;

class Command implements EventMachineDescription
{
    public const ADD_FLIGHT = 'AddFlight';
    public const RESERVE_TICKET = 'ReserveTicket';

    public const BLOCK_SEAT = 'BlockSeat';

    public static function describe(EventMachine $eventMachine): void
    {
        $eventMachine->registerCommand(self::ADD_FLIGHT, JsonSchema::object([
            Payload::FLIGHT_ID => Schema::id(),
            Payload::NUMBER => Schema::flightNumber()
        ]))->registerCommand(self::RESERVE_TICKET, JsonSchema::object([
            Payload::RESERVATION_ID => Schema::id(),
            Payload::USER_ID => Schema::id(),
            Payload::FLIGHT_ID => Schema::id(),
            Payload::SEAT => Schema::seat()
        ]))->registerCommand(self::BLOCK_SEAT, JsonSchema::object([
            Payload::FLIGHT_ID => Schema::id(),
            Payload::SEAT => Schema::seat(),
            Payload::VERSION => Schema::version()
        ]))
        ;
    }
}
