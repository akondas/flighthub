<?php

declare(strict_types=1);

namespace FlightHub\Infrastructure\Finder;

use FlightHub\Application\Payload;
use FlightHub\Application\Query;
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

    public function __invoke($flightQuery, Deferred $deferred): void
    {
        switch (get_class($flightQuery)) {
            case Query\FindFlight::class:
                $this->resolveFlight($flightQuery, $deferred);
                break;
            case Query\FindFlights::class:
                $this->resolveFlights($flightQuery, $deferred);
                break;
        }
    }

    private function resolveFlight(Query\FindFlight $query, Deferred $deferred): void
    {
        $flightDoc = $this->documentStore->getDoc($this->collectionName, $query->flightId());

        if (!$flightDoc) {
            $deferred->reject(new \RuntimeException('Flight not found', 404));

            return;
        }

        $deferred->resolve($this->hydrateFlight($flightDoc));
    }

    private function resolveFlights(Query\FindFlights $query, Deferred $deferred): array
    {
        $filter = $query->number() ?
            new DocumentStore\Filter\LikeFilter(Payload::NUMBER, '%'.$query->number().'%')
            : new DocumentStore\Filter\AnyFilter();

        $cursor = $this->documentStore->filterDocs($this->collectionName, $filter);

        $deferred->resolve(array_map(function ($doc) {
            return $this->hydrateFlight($doc);
        }, iterator_to_array($cursor)));
    }

    private function hydrateFlight(array $doc): array
    {
        return [
            'flightId' => $doc['id'],
            'number' => $doc['number']
        ];
    }
}
