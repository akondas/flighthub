<?php

declare(strict_types=1);

namespace FlightHub\Infrastructure\Handler;

use FlightHub\Api\Event;
use FlightHub\Api\Payload;
use FlightHub\Domain\Flight;
use Prooph\EventMachine\Messaging\Message;

final class FlightHandler
{
    public static function add(Message $addFlight): \Generator
    {
        yield from Flight::add($addFlight->get(Payload::FLIGHT_ID), $addFlight->get(Payload::NUMBER));
    }

    public static function whenFlightAdded(Message $flightAdded): Flight
    {
        return Flight::whenFlightAdded($flightAdded);
    }

    public static function reserveTicket(Flight $flight, Message $reserveTicket): \Generator
    {
        yield from $flight->reserveTicket(
            $reserveTicket->get(Payload::RESERVATION_ID),
            $reserveTicket->get(Payload::USER_ID),
            $reserveTicket->get(Payload::SEAT)
        );
    }

    public static function blockSeat(Flight $flight, Message $blockSeat): \Generator
    {
        yield from $flight->blockSeat($blockSeat->get(Payload::SEAT), $blockSeat->get(Payload::VERSION));
    }

    public static function apply(Flight $flight, Message $event): Flight
    {
        switch ($event->messageName()) {
            case Event::TICKET_RESERVED:
                $flight->whenTicketReserved($event);
                break;
            case Event::SEAT_BLOCKED:
                $flight->whenSeatBlocked($event);
                break;
        }

        return $flight;
    }
}
