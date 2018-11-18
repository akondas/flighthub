<?php

declare(strict_types=1);

namespace FlightHub\Domain\Flight;

final class Reservation
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $userId;

    /**
     * @var string
     */
    private $seat;

    public function __construct(string $id, string $userId, string $seat)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->seat = $seat;
    }
}
