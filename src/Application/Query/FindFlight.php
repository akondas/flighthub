<?php

declare(strict_types=1);

namespace FlightHub\Application\Query;

final class FindFlight
{
    /**
     * @var string
     */
    private $flightId;

    public function __construct(string $flightId)
    {
        $this->flightId = $flightId;
    }

    public function flightId(): string
    {
        return $this->flightId;
    }
}
