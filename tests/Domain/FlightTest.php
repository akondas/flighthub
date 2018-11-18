<?php

declare(strict_types=1);

namespace FlightHubTest\Domain;

use FlightHub\Domain\Event\FlightAdded;
use FlightHub\Domain\Exception\FlightConcurrencyException;
use FlightHub\Domain\Flight;
use FlightHubTest\BaseTestCase;

final class FlightTest extends BaseTestCase
{
    public function testAllowToCreateFlight(): void
    {
        $flight = Flight::add('cd3b6c59-4f0a-4edb-962b-6f76f8e1bb28', 'KL445');

        self::assertRecordedEvent(
            new FlightAdded('cd3b6c59-4f0a-4edb-962b-6f76f8e1bb28', 'KL445'),
            $flight
        );
    }

    public function testNotAllowToReserveNotAvailableSeat(): void
    {
        $flight = Flight::add('cd3b6c59-4f0a-4edb-962b-6f76f8e1bb28', 'KL445');
        self::applyEvents($flight);

        $flight->reserveTicket(
            '40ba879f-e9db-4c15-9828-5bdb7e7f1010',
            '7287e8eb-3ec7-43a3-8d07-f579272f2a6f',
            '24A'
        );
        self::applyEvents($flight);

        self::expectException(\DomainException::class);

        $flight->reserveTicket(
            '40ba879f-e9db-4c15-9828-5bdb7e7f1010',
            '7287e8eb-3ec7-43a3-8d07-f579272f2a6f',
            '24A'
        );
    }

    public function testVerifyOptimisticOfflineLockWhenTryToBlockSeat(): void
    {
        $flight = Flight::add('cd3b6c59-4f0a-4edb-962b-6f76f8e1bb28', 'KL445');
        self::applyEvents($flight);

        $flight->blockSeat('24A', 1);
        self::applyEvents($flight);
        ;

        self::expectException(FlightConcurrencyException::class);

        $flight->blockSeat('25B', 1);
    }

    public function testNotAllowToBlockAlreadyReservedSeat(): void
    {
        $flight = Flight::add('cd3b6c59-4f0a-4edb-962b-6f76f8e1bb28', 'KL445');
        self::applyEvents($flight);

        $flight->reserveTicket(
            '40ba879f-e9db-4c15-9828-5bdb7e7f1010',
            '7287e8eb-3ec7-43a3-8d07-f579272f2a6f',
            '24A'
        );
        self::applyEvents($flight);

        self::expectException(\DomainException::class);

        $flight->blockSeat('24A', 2);
    }
}
