<?php

declare(strict_types=1);

namespace FlightHubTest\Domain;

use FlightHub\Api\Event;
use FlightHub\Api\Payload;
use FlightHub\Domain\Exception\FlightConcurrencyException;
use FlightHub\Domain\Flight;
use FlightHubTest\BaseTestCase;

final class FlightTest extends BaseTestCase
{
    public function testAllowToCreateFlight(): void
    {
        $events = iterator_to_array(
            Flight::add('cd3b6c59-4f0a-4edb-962b-6f76f8e1bb28', 'KL445')
        );

        $this->assertRecordedEvent(Event::FLIGHT_ADDED, [
            Payload::FLIGHT_ID => 'cd3b6c59-4f0a-4edb-962b-6f76f8e1bb28',
            Payload::NUMBER => 'KL445'
        ], $events);
    }

    public function testNotAllowToReserveNotAvailableSeat(): void
    {
        $flight = Flight::whenFlightAdded($this->message(Event::FLIGHT_ADDED, [
            Payload::FLIGHT_ID => $flightId = 'cd3b6c59-4f0a-4edb-962b-6f76f8e1bb28',
            Payload::NUMBER => 'KL445'
        ]));

        $flight->whenTicketReserved($this->message(Event::TICKET_RESERVED, [
            Payload::RESERVATION_ID => $reservationId = '40ba879f-e9db-4c15-9828-5bdb7e7f1010',
            Payload::USER_ID => $userId = '7287e8eb-3ec7-43a3-8d07-f579272f2a6f',
            Payload::FLIGHT_ID => $flightId,
            Payload::SEAT => $seat = '24A'
        ]));

        self::expectException(\DomainException::class);

        iterator_to_array($flight->reserveTicket($reservationId, $userId, $seat));
    }

    public function testVerifyOptimisticOfflineLockWhenTryToBlockSeat(): void
    {
        $flight = Flight::whenFlightAdded($this->message(Event::FLIGHT_ADDED, [
            Payload::FLIGHT_ID => $flightId = 'cd3b6c59-4f0a-4edb-962b-6f76f8e1bb28',
            Payload::NUMBER => 'KL445'
        ]));

        $flight->whenSeatBlocked($this->message(Event::SEAT_BLOCKED, [
            Payload::FLIGHT_ID => $flightId,
            Payload::SEAT => $seat = '24A',
            Payload::VERSION => 1
        ]));

        self::expectException(FlightConcurrencyException::class);

        iterator_to_array($flight->blockSeat('25B', 1));
    }

    public function testNotAllowToBlockAlreadyReservedSeat(): void
    {
        $flight = Flight::whenFlightAdded($this->message(Event::FLIGHT_ADDED, [
            Payload::FLIGHT_ID => $flightId = 'cd3b6c59-4f0a-4edb-962b-6f76f8e1bb28',
            Payload::NUMBER => 'KL445'
        ]));

        $flight->whenTicketReserved($this->message(Event::TICKET_RESERVED, [
            Payload::RESERVATION_ID => $reservationId = '40ba879f-e9db-4c15-9828-5bdb7e7f1010',
            Payload::USER_ID => $userId = '7287e8eb-3ec7-43a3-8d07-f579272f2a6f',
            Payload::FLIGHT_ID => $flightId,
            Payload::SEAT => $seat = '24A'
        ]));

        self::expectException(\DomainException::class);

        iterator_to_array($flight->blockSeat('24A', 2));
    }
}
