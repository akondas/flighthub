<?php

declare(strict_types=1);

namespace FlightHub\Application\Query;

final class FindFlights
{
    /**
     * @var string|null
     */
    private $number;

    public function __construct(?string $number = null)
    {
        $this->number = $number;
    }

    public function number(): ?string
    {
        return $this->number;
    }
}
