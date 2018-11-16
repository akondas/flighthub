<?php

declare(strict_types=1);

namespace FlightHubTest\Domain;

use FlightHub\Api\Command;
use FlightHub\Api\Event;
use FlightHub\Api\Payload;
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
            Payload::ID => 'cd3b6c59-4f0a-4edb-962b-6f76f8e1bb28',
            Payload::NUMBER => 'KL445'
        ]);

        $events = iterator_to_array(
            Flight::add($command)
        );

        $this->assertRecordedEvent(Event::FLIGHT_ADDED, [
            Payload::ID => 'cd3b6c59-4f0a-4edb-962b-6f76f8e1bb28',
            Payload::NUMBER => 'KL445'
        ], $events);
    }
}
