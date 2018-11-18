<?php

declare(strict_types=1);

namespace FlightHub\Application;

use FlightHub\Application\Query\FindFlight;
use FlightHub\Application\Query\FindFlights;
use FlightHub\Application\Query\HealthCheck;

final class Query
{
    public const HEALTH_CHECK = 'HealthCheck';
    public const FLIGHT = 'FindFlight';
    public const FLIGHTS = 'FindFlights';

    private const CLASS_MAP = [
        self::HEALTH_CHECK => HealthCheck::class,
        self::FLIGHT => FindFlight::class,
        self::FLIGHTS => FindFlights::class
    ];

    public static function getClass(string $query): string
    {
        if (!isset(self::CLASS_MAP[$query])) {
            throw new \RuntimeException(sprintf('Query type %s not supported', $query));
        }

        return self::CLASS_MAP[$query];
    }
}
