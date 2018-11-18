<?php

declare(strict_types=1);

namespace FlightHub\Domain;

abstract class Aggregate
{
    /**
     * @var Event[]
     */
    private $recordedEvents = [];

    abstract public function apply(Event $event): void;

    /**
     * @return Event[]
     */
    final public function popRecordedEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }

    final public static function reconstituteFromHistory(iterable $history): self
    {
        $self = new static();
        foreach ($history as $event) {
            $self->apply($event);
        }

        return $self;
    }

    final protected function recordThat(Event $event): void
    {
        $this->recordedEvents[] = $event;
    }

    final protected function __construct()
    {
    }
}
