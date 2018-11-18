<?php

declare(strict_types=1);

namespace FlightHub\Domain\Event;

use FlightHub\Domain\Event;

final class TicketReserved implements Event
{
    /**
     * @var string
     */
    private $reservationId;

    /**
     * @var string
     */
    private $flightId;

    /**
     * @var string
     */
    private $userId;

    /**
     * @var string
     */
    private $seat;

    public function __construct(string $reservationId, string $flightId, string $userId, string $seat)
    {
        $this->reservationId = $reservationId;
        $this->flightId = $flightId;
        $this->userId = $userId;
        $this->seat = $seat;
    }

    public function reservationId(): string
    {
        return $this->reservationId;
    }

    public function flightId(): string
    {
        return $this->flightId;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function seat(): string
    {
        return $this->seat;
    }
}
