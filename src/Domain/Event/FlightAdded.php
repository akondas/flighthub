<?php

declare(strict_types=1);

namespace FlightHub\Domain\Event;

use FlightHub\Domain\Event;

final class FlightAdded implements Event
{
    /**
     * @var string
     */
    private $flightId;

    /**
     * @var string
     */
    private $number;

    public function __construct(string $flightId, string $number)
    {
        $this->flightId = $flightId;
        $this->number = $number;
    }

    public function flightId(): string
    {
        return $this->flightId;
    }

    public function number(): string
    {
        return $this->number;
    }
}
