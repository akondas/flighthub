<?php

declare(strict_types=1);

namespace FlightHub\Application;

use FlightHub\Application\Command\AddFlight;
use FlightHub\Application\Command\BlockSeat;
use FlightHub\Application\Command\ReserveTicket;

final class Command
{
    public const ADD_FLIGHT = 'AddFlight';
    public const RESERVE_TICKET = 'ReserveTicket';
    public const BLOCK_SEAT = 'BlockSeat';

    private const CLASS_MAP = [
        self::ADD_FLIGHT => AddFlight::class,
        self::RESERVE_TICKET => ReserveTicket::class,
        self::BLOCK_SEAT => BlockSeat::class
    ];

    public static function getClass(string $command): string
    {
        if (!isset(self::CLASS_MAP[$command])) {
            throw new \RuntimeException(sprintf('Command type %s not supported', $command));
        }

        return self::CLASS_MAP[$command];
    }
}
