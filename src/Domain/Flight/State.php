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

    /**
     * @var string[]
     */
    private $blockedSeats = [];

    /**
     * @var int
     */
    private $version = 1;

    private static function arrayPropItemTypeMap(): array
    {
        return [
            'reservations' => Type::RESERVATION,
            'blockedSeats' => self::PHP_TYPE_STRING
        ];
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

    public function blockedSeats(): array
    {
        return array_keys($this->blockedSeats);
    }

    public function version(): int
    {
        return $this->version;
    }

    public function withReservation(Reservation $reservation): State
    {
        $copy = clone $this;
        $copy->reservations[$reservation->seat()] = $reservation;
        ++$copy->version;

        return $copy;
    }

    public function withBlockedSeat(string $seat): State
    {
        $copy = clone $this;
        $copy->blockedSeats[$seat] = true;
        ++$copy->version;

        return $copy;
    }

    public function isSeatAvailable(string $seat): bool
    {
        return !isset($this->reservations[$seat]) && !isset($this->blockedSeats[$seat]);
    }
}
