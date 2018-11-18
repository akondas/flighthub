<?php

declare(strict_types=1);

namespace FlightHub\Application;

use FlightHub\Domain\Flight;

final class Aggregate
{
    public const FLIGHT = 'Flight';

    private const CLASS_MAP = [
        self::FLIGHT => Flight::class
    ];

    public static function getClass(string $aggregate): string
    {
        if (!isset(self::CLASS_MAP[$aggregate])) {
            throw new \RuntimeException(sprintf('Aggregate type %s not supported', $aggregate));
        }

        return self::CLASS_MAP[$aggregate];
    }
}
