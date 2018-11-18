<?php

declare(strict_types=1);

namespace FlightHub\Api;

use Prooph\EventMachine\EventMachine;
use Prooph\EventMachine\EventMachineDescription;
use Prooph\EventMachine\JsonSchema\JsonSchema;

class Event implements EventMachineDescription
{
    public const FLIGHT_ADDED = 'FlightAdded';
    public const TICKET_RESERVED = 'TicketReserved';

    public const SEAT_BLOCKED = 'SeatBlocked';

    public static function describe(EventMachine $eventMachine): void
    {
        $eventMachine->registerEvent(
            self::FLIGHT_ADDED,
            JsonSchema::object([
                Payload::FLIGHT_ID => Schema::id(),
                Payload::NUMBER => Schema::flightNumber()
            ])
        )->registerEvent(
            self::TICKET_RESERVED,
            JsonSchema::object([
                Payload::RESERVATION_ID => Schema::id(),
                Payload::USER_ID => Schema::id(),
                Payload::FLIGHT_ID => Schema::id(),
                Payload::SEAT => Schema::seat()
            ])
        );
    }
}
