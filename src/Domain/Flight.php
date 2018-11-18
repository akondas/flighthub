<?php

declare(strict_types=1);

namespace FlightHub\Domain;

use FlightHub\Api\Event;
use FlightHub\Api\Payload;
use FlightHub\Domain\Exception\FlightConcurrencyException;
use FlightHub\Domain\Flight\Reservation;
use FlightHub\Domain\Flight\State;
use Prooph\EventMachine\Messaging\Message;

final class Flight
{
    public static function add(Message $addFlight): \Generator
    {
        yield [Event::FLIGHT_ADDED, $addFlight->payload()];
    }

    public static function whenFlightAdded(Message $flightAdded): State
    {
        return State::fromArray($flightAdded->payload());
    }

    public static function reserveTicket(State $state, Message $reserveTicket): \Generator
    {
        if (!$state->isSeatAvailable($reserveTicket->get(Payload::SEAT))) {
            throw new \DomainException(sprintf('Seat %s is not available', $reserveTicket->get(Payload::SEAT)));
        }

        yield [Event::TICKET_RESERVED, $reserveTicket->payload()];
    }

    public static function whenTicketReserved(State $state, Message $reserveTicket): State
    {
        return $state->withReservation(new Reservation(
            $reserveTicket->get(Payload::RESERVATION_ID),
            $reserveTicket->get(Payload::USER_ID),
            $reserveTicket->get(Payload::SEAT)
        ));
    }

    public static function blockSeat(State $state, Message $blockSeat): \Generator
    {
        if ($state->version() !== $blockSeat->get(Payload::VERSION)) {
            throw new FlightConcurrencyException(sprintf('Flight %s has been modified', $blockSeat->get(Payload::FLIGHT_ID)));
        }

        if (!$state->isSeatAvailable($blockSeat->get(Payload::SEAT))) {
            throw new \DomainException(sprintf('Seat %s is not available', $blockSeat->get(Payload::SEAT)));
        }

        yield [Event::SEAT_BLOCKED, $blockSeat->payload()];
    }

    public static function whenSeatBlocked(State $state, Message $seatBlocked): State
    {
        return $state->withBlockedSeat($seatBlocked->get(Payload::SEAT));
    }
}
