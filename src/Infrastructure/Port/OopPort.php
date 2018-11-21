<?php

declare(strict_types=1);

namespace FlightHub\Infrastructure\Port;

use FlightHub\Application\Aggregate;
use FlightHub\Application\Command\BlockSeat;
use FlightHub\Application\Command\ReserveTicket;
use FlightHub\Domain\Event;
use FlightHub\Domain\Flight;
use FlightHub\Domain\Aggregate as DomainAggregate;
use Prooph\EventMachine\Exception\InvalidArgumentException;
use Prooph\EventMachine\Runtime\Oop\Port;
use Symfony\Component\Serializer\Serializer;

final class OopPort implements Port
{
    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function callAggregateFactory(string $aggregateType, callable $aggregateFactory, $customCommand, $context = null)
    {
        switch (Aggregate::getClass($aggregateType)) {
            case Flight::class:
                return Flight::add($customCommand->flightId(), $customCommand->number());
            default:
                throw new InvalidArgumentException(sprintf('Unknown aggregate: %s', $aggregateType));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function callAggregateWithCommand($aggregate, $customCommand, $context = null): void
    {
        switch (\get_class($customCommand)) {
            case ReserveTicket::class:
                /** @var Flight $aggregate */
                $aggregate->reserveTicket($customCommand->reservationId(), $customCommand->userId(), $customCommand->seat());
                break;
            case BlockSeat::class:
                /** @var Flight $aggregate */
                $aggregate->blockSeat($customCommand->seat(), $customCommand->version());
                break;
            default:
                throw new InvalidArgumentException(sprintf('Unknown command: %s'.\get_class($customCommand)));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function popRecordedEvents($aggregate): array
    {
        if (!$aggregate instanceof DomainAggregate) {
            throw new InvalidArgumentException(sprintf('Aggregate %s must be instance of %s', \get_class($aggregate), DomainAggregate::class));
        }

        return $aggregate->popRecordedEvents();
    }

    /**
     * {@inheritdoc}
     */
    public function applyEvent($aggregate, $customEvent): void
    {
        if (!$aggregate instanceof DomainAggregate) {
            throw new InvalidArgumentException(sprintf('Aggregate %s must be instance of %s', \get_class($aggregate), DomainAggregate::class));
        }

        if (!$customEvent instanceof Event) {
            throw new InvalidArgumentException(sprintf('Event %s must be instance of %s', \get_class($customEvent), Event::class));
        }

        $aggregate->apply($customEvent);
    }

    /**
     * {@inheritdoc}
     */
    public function serializeAggregate($aggregate): array
    {
        return $this->serializer->normalize($aggregate);
    }

    /**
     * {@inheritdoc}
     */
    public function reconstituteAggregate(string $aggregateType, iterable $events)
    {
        $aggregateClass = Aggregate::getClass($aggregateType);

        return $aggregateClass::reconstituteFromHistory($events);
    }
}
