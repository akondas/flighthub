<?php

declare(strict_types=1);

namespace FlightHub\Infrastructure\System;

use FlightHub\Application\Query\HealthCheck;
use React\Promise\Deferred;

final class HealthCheckResolver
{
    public function __invoke(HealthCheck $query, Deferred $deferred): void
    {
        $deferred->resolve([
            'system' => true
        ]);
    }
}
