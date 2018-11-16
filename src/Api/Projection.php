<?php

declare(strict_types=1);

namespace FlightHub\Api;

use Prooph\EventMachine\EventMachine;
use Prooph\EventMachine\EventMachineDescription;
use Prooph\EventMachine\Persistence\Stream;

class Projection implements EventMachineDescription
{
    public static function describe(EventMachine $eventMachine): void
    {
        $eventMachine->watch(Stream::ofWriteModel())
            ->withAggregateProjection(Aggregate::FLIGHT);
    }
}
