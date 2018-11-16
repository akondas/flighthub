<?php

declare(strict_types=1);

namespace FlightHub\Infrastructure\Finder;

use FlightHub\Api\Payload;
use FlightHub\Api\Query;
use Prooph\EventMachine\Messaging\Message;
use Prooph\EventMachine\Persistence\DocumentStore;
use React\Promise\Deferred;

final class FlightFinder
{
    /**
     * @var DocumentStore
     */
    private $documentStore;

    /**
     * @var string
     */
    private $collectionName;

    public function __construct(string $collectionName, DocumentStore $documentStore)
    {
        $this->collectionName = $collectionName;
        $this->documentStore = $documentStore;
    }

    public function __invoke(Message $flightQuery, Deferred $deferred): void
    {
        switch ($flightQuery->messageName()) {
            case Query::FLIGHT:
                $this->resolveFlight($deferred, $flightQuery->get(Payload::ID));
                break;
            case Query::FLIGHTS:
                $this->resolveFlights($deferred, $flightQuery->getOrDefault(Payload::NUMBER, null));
                break;
        }
    }

    private function resolveFlight(Message $flightQuery, Deferred $deferred): void
    {
        $flightDoc = $this->documentStore->getDoc($this->collectionName, $flightQuery->get(Payload::ID));

        if (!$flightDoc) {
            $deferred->reject(new \RuntimeException('Flight not found', 404));

            return;
        }

        $deferred->resolve($flightDoc);
    }

    private function resolveFlights(Deferred $deferred, string $numberFilter = null): array
    {
        $filter = $numberFilter?
            new DocumentStore\Filter\LikeFilter(Payload::NUMBER, "%$numberFilter%")
            : new DocumentStore\Filter\AnyFilter();

        $cursor = $this->documentStore->filterDocs($this->collectionName, $filter);

        $deferred->resolve(iterator_to_array($cursor));
    }
}
