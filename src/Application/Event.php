<?php

declare(strict_types=1);

namespace FlightHub\Application;

use FlightHub\Domain\Event\FlightAdded;
use FlightHub\Domain\Event\SeatBlocked;
use FlightHub\Domain\Event\TicketReserved;

final class Event
{
    public const FLIGHT_ADDED = 'FlightAdded';
    public const TICKET_RESERVED = 'TicketReserved';
    public const SEAT_BLOCKED = 'SeatBlocked';

    private const CLASS_MAP = [
        self::FLIGHT_ADDED => FlightAdded::class,
        self::TICKET_RESERVED => TicketReserved::class,
        self::SEAT_BLOCKED => SeatBlocked::class
    ];

    public static function getClass(string $event): string
    {
        if (!isset(self::CLASS_MAP[$event])) {
            throw new \RuntimeException(sprintf('Event type %s not supported', $event));
        }

        return self::CLASS_MAP[$event];
    }

    public static function getType(string $class): string
    {
        $map = \array_flip(self::CLASS_MAP);

        if (!isset($map[$class])) {
            throw new \RuntimeException(sprintf('Event class %s not supported', $class));
        }

        return $map[$class];
    }
}
