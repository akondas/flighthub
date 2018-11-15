<?php

declare(strict_types=1);

namespace FlightHub\Infrastructure\System;

use Prooph\EventMachine\Messaging\Message;
use React\Promise\Deferred;

final class HealthCheckResolver
{
    public function __invoke(Message $healthCheck, Deferred $deferred): void
    {
        $deferred->resolve([
            'system' => true
        ]);
    }
}
