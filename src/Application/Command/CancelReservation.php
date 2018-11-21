<?php

declare(strict_types=1);

namespace FlightHub\Application\Command;

final class CancelReservation
{
    /**
     * @var string
     */
    private $reservationId;

    public function __construct(string $reservationId)
    {
        $this->reservationId = $reservationId;
    }

    public function reservationId(): string
    {
        return $this->reservationId;
    }
}
