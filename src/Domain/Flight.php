<?php

declare(strict_types=1);

namespace FlightHub\Domain;

use FlightHub\Domain\Event\FlightAdded;
use FlightHub\Domain\Event\SeatBlocked;
use FlightHub\Domain\Event\TicketReserved;
use FlightHub\Domain\Exception\FlightConcurrencyException;
use FlightHub\Domain\Exception\RuntimeException;
use FlightHub\Domain\Flight\Reservation;

final class Flight extends Aggregate
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $number;

    /**
     * @var Reservation[]
     */
    private $reservations = [];

    /**
     * @var bool[]
     */
    private $blockedSeats = [];

    /**
     * @var int
     */
    private $version = 1;

    public static function add(string $flightId, string $number): self
    {
        $self = new self();

        $self->recordThat(
            new FlightAdded($flightId, $number)
        );

        return $self;
    }

    public function reserveTicket(string $reservationId, string $userId, string $seat): void
    {
        if (!$this->isSeatAvailable($seat)) {
            throw new \DomainException(sprintf('Seat %s is not available', $seat));
        }

        $this->recordThat(new TicketReserved(
            $reservationId,
            $this->id,
            $userId,
            $seat
        ));
    }

    public function blockSeat(string $seat, int $version): void
    {
        if ($this->version !== $version) {
            throw new FlightConcurrencyException(sprintf('Flight %s has been modified', $this->id));
        }

        if (!$this->isSeatAvailable($seat)) {
            throw new \DomainException(sprintf('Seat %s is not available', $seat));
        }

        $this->recordThat(new SeatBlocked($this->id, $seat));
    }

    public function apply(Event $event): void
    {
        switch (\get_class($event)) {
            case FlightAdded::class:
                /** @var FlightAdded $event */
                $this->id = $event->flightId();
                $this->number = $event->number();
                break;

            case TicketReserved::class:
                /** @var TicketReserved $event */
                $this->reservations[$event->seat()] = new Reservation(
                    $event->reservationId(),
                    $event->userId(),
                    $event->seat()
                );
                ++$this->version;
                break;

            case SeatBlocked::class:
                /** @var SeatBlocked $event */
                $this->blockedSeats[$event->seat()] = true;
                ++$this->version;
                break;

            default:
                throw new RuntimeException('Unknown event: '.\get_class($event));
        }
    }

    private function isSeatAvailable(string $seat): bool
    {
        return !isset($this->reservations[$seat]) && !isset($this->blockedSeats[$seat]);
    }
}
