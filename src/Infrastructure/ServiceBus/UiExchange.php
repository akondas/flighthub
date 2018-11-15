<?php

declare(strict_types=1);

namespace FlightHub\Infrastructure\ServiceBus;

use Prooph\EventMachine\Messaging\Message;

/**
 * Marker Interface UiExchange
 *
 * @package FlightHub\Infrastructure\ServiceBus
 */
interface UiExchange
{
    public function __invoke(Message $event): void;
}
