<?php

declare(strict_types=1);

namespace FlightHub\Domain\Flight;

use Prooph\EventMachine\Data\ImmutableRecord;
use Prooph\EventMachine\Data\ImmutableRecordLogic;

final class Reservation implements ImmutableRecord
{
    use ImmutableRecordLogic;

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

    public function id(): string
    {
        return $this->id;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function seat(): string
    {
        return $this->seat;
    }
}
