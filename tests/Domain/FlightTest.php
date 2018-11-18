<?php

declare(strict_types=1);

namespace FlightHubTest\Domain;

use FlightHub\Api\Command;
use FlightHub\Api\Event;
use FlightHub\Api\Payload;
use FlightHub\Domain\Exception\FlightConcurrencyException;
use FlightHub\Domain\Flight;
use FlightHubTest\BaseTestCase;

final class FlightTest extends BaseTestCase
{
    /**
     * @test
     */
    public function it_allow_to_create_flight(): void
    {
        $command = $this->message(Command::ADD_FLIGHT, [
            Payload::FLIGHT_ID => 'cd3b6c59-4f0a-4edb-962b-6f76f8e1bb28',
            Payload::NUMBER => 'KL445'
        ]);

        $events = iterator_to_array(
            Flight::add($command)
        );

        $this->assertRecordedEvent(Event::FLIGHT_ADDED, [
            Payload::FLIGHT_ID => 'cd3b6c59-4f0a-4edb-962b-6f76f8e1bb28',
            Payload::NUMBER => 'KL445'
        ], $events);
    }

    /**
     * @test
     */
    public function it_not_allow_to_reserve_not_available_seat(): void
    {
        $state = Flight\State::fromArray([
            Payload::FLIGHT_ID => $flightId = 'cd3b6c59-4f0a-4edb-962b-6f76f8e1bb28',
            Payload::NUMBER => 'KL445'
        ]);

        $state = $state->withReservation(new Flight\Reservation(
            'bf4a1753-54bf-44c5-8252-f1db3d529a27',
            '8039a438-1e60-49c6-ba13-c75b4cdad5b6',
            '24A'
        ));

        $command = $this->message(Command::RESERVE_TICKET, [
            Payload::RESERVATION_ID => '40ba879f-e9db-4c15-9828-5bdb7e7f1010',
            Payload::USER_ID => '7287e8eb-3ec7-43a3-8d07-f579272f2a6f',
            Payload::FLIGHT_ID => $flightId,
            Payload::SEAT => '24A'
        ]);

        self::expectException(\DomainException::class);

        iterator_to_array(Flight::reserveTicket($state, $command));
    }

    /**
     * @test
     */
    public function it_verify_optimistic_offline_lock_when_try_to_block_seat(): void
    {
        $state = Flight\State::fromArray([
            Payload::FLIGHT_ID => $flightId = 'cd3b6c59-4f0a-4edb-962b-6f76f8e1bb28',
            Payload::NUMBER => 'KL445'
        ]);

        $state = $state->withBlockedSeat('24A');

        $command = $this->message(Command::BLOCK_SEAT, [
            Payload::FLIGHT_ID => $flightId,
            Payload::SEAT => '25C',
            Payload::VERSION => 1
        ]);

        self::expectException(FlightConcurrencyException::class);

        iterator_to_array(Flight::blockSeat($state, $command));
    }

    /**
     * @test
     */
    public function it_not_allow_to_block_already_reserved_seat(): void
    {
        $state = Flight\State::fromArray([
            Payload::FLIGHT_ID => $flightId = 'cd3b6c59-4f0a-4edb-962b-6f76f8e1bb28',
            Payload::NUMBER => 'KL445'
        ]);

        $state = $state->withReservation(new Flight\Reservation(
            'bf4a1753-54bf-44c5-8252-f1db3d529a27',
            '8039a438-1e60-49c6-ba13-c75b4cdad5b6',
            '24A'
        ));

        $command = $this->message(Command::BLOCK_SEAT, [
            Payload::FLIGHT_ID => $flightId,
            Payload::SEAT => '24A',
            Payload::VERSION => 2
        ]);

        self::expectException(\DomainException::class);

        iterator_to_array(Flight::blockSeat($state, $command));
    }
}
