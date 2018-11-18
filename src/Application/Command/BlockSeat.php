<?php
declare(strict_types=1);


namespace FlightHub\Application\Command;


final class BlockSeat
{
    /**
     * @var string
     */
    private $flightId;

    /**
     * @var string
     */
    private $seat;

    /**
     * @var int
     */
    private $version;

    public function __construct(string $flightId, string $seat, int $version)
    {
        $this->flightId = $flightId;
        $this->seat = $seat;
        $this->version = $version;
    }

    public function flightId(): string
    {
        return $this->flightId;
    }

    public function seat(): string
    {
        return $this->seat;
    }

    public function version(): int
    {
        return $this->version;
    }


}
