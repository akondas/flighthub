<?php

declare(strict_types=1);

namespace FlightHub\Domain\Event;

use FlightHub\Domain\Event;

final class SeatBlocked implements Event
{
    /**
     * @var string
     */
    private $flightId;

    /**
     * @var string
     */
    private $seat;

    public function __construct(string $flightId, string $seat)
    {
        $this->flightId = $flightId;
        $this->seat = $seat;
    }

    public function flightId(): string
    {
        return $this->flightId;
    }

    public function seat(): string
    {
        return $this->seat;
    }
}
