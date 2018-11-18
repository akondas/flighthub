<?php

declare(strict_types=1);

namespace FlightHubTest;

use FlightHub\Domain\Aggregate;
use FlightHub\Domain\Event;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;

class BaseTestCase extends TestCase
{
    protected static function applyEvents(Aggregate $aggregate): void
    {
        foreach ($aggregate->popRecordedEvents() as $event) {
            $aggregate->apply($event);
        }
    }

    protected static function assertRecordedEvent(Event $event, Aggregate $aggregate, bool $assertNotRecorded = false): void
    {
        $propertyNormalizer = new PropertyNormalizer();
        $events = $aggregate->popRecordedEvents();
        $recorded = false;

        foreach ($events as $evt) {
            if (\get_class($event) === \get_class($evt) && $propertyNormalizer->normalize($event) === $propertyNormalizer->normalize($evt)) {
                $recorded = true;
                break;
            }
        }

        if ($assertNotRecorded) {
            self::assertFalse($recorded, sprintf('Event %s is recorded', \get_class($event)));
        } else {
            self::assertTrue($recorded, sprintf('Event %s is not recorded', \get_class($event)));
        }
    }

    protected static function assertNotRecordedEvent(Event $event, Aggregate $aggregate): void
    {
        self::assertRecordedEvent($event, $aggregate, true);
    }
}
