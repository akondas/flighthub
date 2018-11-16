<?php

declare(strict_types=1);

namespace FlightHub\Domain\Flight;

use Prooph\EventMachine\Data\ImmutableRecord;
use Prooph\EventMachine\Data\ImmutableRecordLogic;

final class State implements ImmutableRecord
{
    use ImmutableRecordLogic;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $number;

    public function id(): string
    {
        return $this->id;
    }

    public function number(): string
    {
        return $this->number;
    }
}
