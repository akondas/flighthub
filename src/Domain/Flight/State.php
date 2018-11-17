<?php

declare(strict_types=1);

namespace FlightHub\Domain\Flight;

use FlightHub\Api\Type;
use Prooph\EventMachine\Data\ImmutableRecord;
use Prooph\EventMachine\Data\ImmutableRecordLogic;

final class State implements ImmutableRecord
{
    use ImmutableRecordLogic;

    /**
     * @var string
     */
    private $flightId;

    /**
     * @var string
     */
    private $number;

    /**
     * @var Reservation[]
     */
    private $reservations = [];

    private static function arrayPropItemTypeMap(): array
    {
        return ['reservations' => Type::RESERVATION];
    }

    public function flightId(): string
    {
        return $this->flightId;
    }

    public function number(): string
    {
        return $this->number;
    }

    /**
     * @return Reservation[]
     */
    public function reservations(): array
    {
        return $this->reservations;
    }

    public function withReservation(Reservation $reservation): State
    {
        $copy = clone $this;
        $copy->reservations[$reservation->seat()] = $reservation;

        return $copy;
    }

    public function isSeatAvailable(string $seat): bool
    {
        return !isset($this->reservations[$seat]);
    }
}
