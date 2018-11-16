<?php

declare(strict_types=1);

namespace FlightHub\Domain;

use FlightHub\Api\Event;
use Prooph\EventMachine\Messaging\Message;

final class Flight
{
    public static function add(Message $addFlight): \Generator
    {
        yield [Event::FLIGHT_ADDED, $addFlight->payload()];
    }

    public static function whenFlightAdded(Message $flightAdded): Flight\State
    {
        return Flight\State::fromArray($flightAdded->payload());
    }
}
