<?php

declare(strict_types=1);

namespace FlightHub\Infrastructure\Port;

use FlightHub\Application\Command;
use FlightHub\Application\Event;
use FlightHub\Application\Query;
use Prooph\EventMachine\Messaging\Message;
use Prooph\EventMachine\Messaging\MessageBag;
use Prooph\EventMachine\Runtime\Functional\Port;
use Symfony\Component\Serializer\Serializer;

final class FunctionalPort implements Port
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
    public function deserialize(Message $message)
    {
        switch ($message->messageType()) {
            case Message::TYPE_COMMAND:
                $className = Command::getClass($message->messageName());
                break;
            case Message::TYPE_EVENT:
                $className = Event::getClass($message->messageName());
                break;
            case Message::TYPE_QUERY:
                $className = Query::getClass($message->messageName());
                break;
            default:
                throw new \RuntimeException(sprintf('Message %s not supported', $message->messageType()));
        }

        return $this->serializer->denormalize($message->payload(), $className);
    }

    /**
     * {@inheritdoc}
     */
    public function serializePayload($customMessage): array
    {
        return $this->serializer->normalize($customMessage);
    }

    /**
     * {@inheritdoc}
     */
    public function decorateEvent($customEvent): MessageBag
    {
        return new MessageBag(
            Event::getType(\get_class($customEvent)),
            MessageBag::TYPE_EVENT,
            $customEvent
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregateIdFromCommand(string $aggregateIdPayloadKey, $command): string
    {
        //Duck typing, do not do this in production but rather use your own interfaces
        return $command->{$aggregateIdPayloadKey}();
    }

    /**
     * {@inheritdoc}
     */
    public function callCommandPreProcessor($customCommand, $preProcessor)
    {
        //Duck typing, do not do this in production but rather use your own interfaces
        return $preProcessor->preProcess($customCommand);
    }

    /**
     * {@inheritdoc}
     */
    public function callContextProvider($customCommand, $contextProvider)
    {
        //Duck typing, do not do this in production but rather use your own interfaces
        return $contextProvider->provide($customCommand);
    }
}
