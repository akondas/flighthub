<?php

declare(strict_types=1);

namespace FlightHub\Domain;

use FlightHub\Api\Event;
use FlightHub\Api\Payload;
use FlightHub\Domain\Exception\FlightConcurrencyException;
use FlightHub\Domain\Flight\Reservation;
use Prooph\EventMachine\Messaging\Message;

final class Flight implements \JsonSerializable
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
     * @var string[]
     */
    private $blockedSeats = [];

    /**
     * @var int
     */
    private $version = 1;

    private function __construct(string $id, string $number)
    {
        $this->id = $id;
        $this->number = $number;
    }

    public static function add(string $id, string $number): \Generator
    {
        yield [Event::FLIGHT_ADDED, [
            Payload::FLIGHT_ID => $id,
            Payload::NUMBER => $number
        ]];
    }

    public static function whenFlightAdded(Message $flightAdded): self
    {
        return new self($flightAdded->get(Payload::FLIGHT_ID), $flightAdded->get(Payload::NUMBER));
    }

    public function reserveTicket(string $reservationId, string $userId, string $seat): \Generator
    {
        if (!$this->isSeatAvailable($seat)) {
            throw new \DomainException(sprintf('Seat %s is not available', $seat));
        }

        yield [Event::TICKET_RESERVED, [
            Payload::FLIGHT_ID => $this->id,
            Payload::RESERVATION_ID => $reservationId,
            Payload::USER_ID => $userId,
            Payload::SEAT => $seat
        ]];
    }

    public function whenTicketReserved(Message $ticketReserved): void
    {
        $this->reservations[$ticketReserved->get(Payload::SEAT)] = new Reservation(
            $ticketReserved->get(Payload::RESERVATION_ID),
            $ticketReserved->get(Payload::USER_ID),
            $ticketReserved->get(Payload::SEAT)
        );
        ++$this->version;
    }

    public function blockSeat(string $seat, int $version): \Generator
    {
        if ($this->version !== $version) {
            throw new FlightConcurrencyException(sprintf('Flight %s has been modified', $this->id));
        }

        if (!$this->isSeatAvailable($seat)) {
            throw new \DomainException(sprintf('Seat %s is not available', $seat));
        }

        yield [Event::SEAT_BLOCKED, [
            Payload::FLIGHT_ID => $this->id,
            Payload::SEAT => $seat,
            Payload::VERSION => $version
        ]];
    }

    public function whenSeatBlocked(Message $seatBlocked): void
    {
        $this->blockedSeats[$seatBlocked->get(Payload::SEAT)] = true;
        ++$this->version;
    }

    private function isSeatAvailable(string $seat): bool
    {
        return !isset($this->reservations[$seat]) && !isset($this->blockedSeats[$seat]);
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'number' => $this->number
        ];
    }
}
