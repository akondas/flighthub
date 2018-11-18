<?php

declare(strict_types=1);

namespace FlightHub\Service;

use FlightHub\Application\Aggregate;
use FlightHub\Http\MessageSchemaMiddleware;
use FlightHub\Infrastructure\Finder\FlightFinder;
use FlightHub\Infrastructure\Logger\PsrErrorLogger;
use FlightHub\Infrastructure\Port\FunctionalPort;
use FlightHub\Infrastructure\Port\OopPort;
use FlightHub\Infrastructure\ServiceBus\CommandBus;
use FlightHub\Infrastructure\ServiceBus\EventBus;
use FlightHub\Infrastructure\ServiceBus\QueryBus;
use FlightHub\Infrastructure\ServiceBus\UiExchange;
use FlightHub\Infrastructure\System\HealthCheckResolver;
use Codeliner\ArrayReader\ArrayReader;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Prooph\Common\Event\ProophActionEventEmitter;
use Prooph\Common\Messaging\NoOpMessageConverter;
use Prooph\EventMachine\Container\ContainerChain;
use Prooph\EventMachine\Container\EventMachineContainer;
use Prooph\EventMachine\Container\ServiceRegistry;
use Prooph\EventMachine\EventMachine;
use Prooph\EventMachine\Http\MessageBox;
use Prooph\EventMachine\Messaging\Message;
use Prooph\EventMachine\Persistence\DocumentStore;
use Prooph\EventMachine\Postgres\PostgresDocumentStore;
use Prooph\EventMachine\Projecting\AggregateProjector;
use Prooph\EventMachine\Runtime\FunctionalFlavour;
use Prooph\EventMachine\Runtime\OopFlavour;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Pdo\PersistenceStrategy;
use Prooph\EventStore\Pdo\PostgresEventStore;
use Prooph\EventStore\Pdo\Projection\PostgresProjectionManager;
use Prooph\EventStore\Projection\ProjectionManager;
use Prooph\EventStore\TransactionalActionEventEmitterEventStore;
use Prooph\ServiceBus\Exception\MessageDispatchException;
use Prooph\ServiceBus\Message\HumusAmqp\AmqpMessageProducer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Zend\Diactoros\Response;
use Zend\ProblemDetails\ProblemDetailsMiddleware;
use Zend\ProblemDetails\ProblemDetailsResponseFactory;

final class ServiceFactory
{
    use ServiceRegistry;

    /**
     * @var ArrayReader
     */
    private $config;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(array $appConfig)
    {
        $this->config = new ArrayReader($appConfig);
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    //Finders
    public function flightFinder(): FlightFinder
    {
        return $this->makeSingleton(FlightFinder::class, function () {
            return new FlightFinder(
                AggregateProjector::aggregateCollectionName(
                    $this->eventMachine()->appVersion(),
                    Aggregate::FLIGHT
                ),
                $this->documentStore()
            );
        });
    }

    //HTTP endpoints
    public function httpMessageBox(): MessageBox
    {
        return $this->makeSingleton(MessageBox::class, function () {
            return $this->eventMachine()->httpMessageBox();
        });
    }

    public function eventMachineHttpMessageSchema(): MessageSchemaMiddleware
    {
        return $this->makeSingleton(MessageSchemaMiddleware::class, function () {
            return new MessageSchemaMiddleware($this->eventMachine());
        });
    }

    public function pdoConnection(): \PDO
    {
        return $this->makeSingleton(\PDO::class, function () {
            $this->assertMandatoryConfigExists('pdo.dsn');
            $this->assertMandatoryConfigExists('pdo.user');
            $this->assertMandatoryConfigExists('pdo.pwd');

            return new \PDO(
                $this->config->stringValue('pdo.dsn'),
                $this->config->stringValue('pdo.user'),
                $this->config->stringValue('pdo.pwd')
            );
        });
    }

    protected function eventStorePersistenceStrategy(): PersistenceStrategy
    {
        return $this->makeSingleton(PersistenceStrategy::class, function () {
            return new PersistenceStrategy\PostgresSingleStreamStrategy();
        });
    }

    public function oopFlavour(): OopFlavour
    {
        $serializer = new Serializer([new PropertyNormalizer()]);

        return new OopFlavour(
            new OopPort($serializer),
            new FunctionalFlavour(new FunctionalPort($serializer))
        );
    }

    public function eventStore(): EventStore
    {
        return $this->makeSingleton(EventStore::class, function () {
            $eventStore = new PostgresEventStore(
                $this->eventMachine()->messageFactory(),
                $this->pdoConnection(),
                $this->eventStorePersistenceStrategy()
            );

            return new TransactionalActionEventEmitterEventStore(
                $eventStore,
                new ProophActionEventEmitter(TransactionalActionEventEmitterEventStore::ALL_EVENTS)
            );
        });
    }

    public function documentStore(): DocumentStore
    {
        return $this->makeSingleton(DocumentStore::class, function () {
            return new PostgresDocumentStore(
                $this->pdoConnection(),
                null,
                'CHAR(36) NOT NULL' //Use alternative docId schema, to allow uuids as well as md5 hashes
            );
        });
    }

    public function projectionManager(): ProjectionManager
    {
        return $this->makeSingleton(ProjectionManager::class, function () {
            return new PostgresProjectionManager(
                $this->eventStore(),
                $this->pdoConnection()
            );
        });
    }

    public function aggregateProjector(): AggregateProjector
    {
        return $this->makeSingleton(AggregateProjector::class, function () {
            return new AggregateProjector(
                $this->documentStore(),
                $this->eventMachine()
            );
        });
    }

    public function commandBus(): CommandBus
    {
        return $this->makeSingleton(CommandBus::class, function () {
            $commandBus = new CommandBus();
            $errorHandler = new \FlightHub\Infrastructure\ServiceBus\ErrorHandler();
            $errorHandler->attachToMessageBus($commandBus);
            return $commandBus;
        });
    }

    public function eventBus(): EventBus
    {
        return $this->makeSingleton(EventBus::class, function () {
            $eventBus = new EventBus();
            $errorHandler = new \FlightHub\Infrastructure\ServiceBus\ErrorHandler();
            $errorHandler->attachToMessageBus($eventBus);
            return $eventBus;
        });
    }

    public function queryBus(): QueryBus
    {
        return $this->makeSingleton(QueryBus::class, function () {
            $queryBus = new QueryBus();
            $errorHandler = new \FlightHub\Infrastructure\ServiceBus\ErrorHandler();
            $errorHandler->attachToMessageBus($queryBus);
            return $queryBus;
        });
    }

    public function uiExchange(): UiExchange
    {
        return $this->makeSingleton(UiExchange::class, function () {
           $this->assertMandatoryConfigExists('rabbit.connection');

            $connection = new \Humus\Amqp\Driver\AmqpExtension\Connection(
                $this->config->arrayValue('rabbit.connection')
            );

            $connection->connect();

            $channel = $connection->newChannel();

            $exchange = $channel->newExchange();

            $exchange->setName($this->config->stringValue('rabbit.ui_exchange', 'ui-exchange'));

            $exchange->setType('fanout');

            $humusProducer = new \Humus\Amqp\JsonProducer($exchange);

            $messageProducer = new \Prooph\ServiceBus\Message\HumusAmqp\AmqpMessageProducer(
                $humusProducer,
                new NoOpMessageConverter()
            );

            return new class($messageProducer) implements UiExchange {
                private $producer;
                public function __construct(AmqpMessageProducer $messageProducer)
                {
                    $this->producer = $messageProducer;
                }

                public function __invoke(Message $event): void
                {
                    $this->producer->__invoke($event);
                }
            };
        });
    }

    public function problemDetailsMiddleware(): ProblemDetailsMiddleware
    {
        return $this->makeSingleton(ProblemDetailsMiddleware::class, function() {
            $isDevEnvironment = $this->config->stringValue('environment', 'prod') === 'dev';

            $problemDetailsResponseFactory = new class(
                function() {
                    return new Response();
                },
                $isDevEnvironment
            ) extends ProblemDetailsResponseFactory {
                public function createResponseFromThrowable(
                    ServerRequestInterface $request,
                    \Throwable $e
                ) : ResponseInterface {
                    if($e instanceof MessageDispatchException) {
                        $e = $e->getPrevious();
                    }

                    return parent::createResponseFromThrowable($request, $e);
                }
            };

            $errorHandler = new ProblemDetailsMiddleware($problemDetailsResponseFactory);
            $errorHandler->attachListener(new PsrErrorLogger($this->logger()));

            return $errorHandler;
        });
    }

    public function logger(): LoggerInterface
    {
        return $this->makeSingleton(LoggerInterface::class, function () {
            $streamHandler = new StreamHandler('php://stderr');

            return new Logger('EventMachine', [$streamHandler]);
        });
    }

    public function healthCheckResolver(): HealthCheckResolver
    {
        return $this->makeSingleton(HealthCheckResolver::class, function () {
            return new HealthCheckResolver();
        });
    }

    public function eventMachine(): EventMachine
    {
        $this->assertContainerIsset();

        return $this->makeSingleton(EventMachine::class, function () {
            //@TODO add config param to enable caching
            $eventMachine = new EventMachine();

            //Load descriptions here or add them to config/autoload/global.php
            foreach ($this->config->arrayValue('event_machine.descriptions') as $desc) {
                $eventMachine->load($desc);
            }

            $containerChain = new ContainerChain(
                $this->container,
                new EventMachineContainer($eventMachine)
            );

            $eventMachine->initialize($containerChain);

            return $eventMachine;
        });
    }

    private function assertContainerIsset(): void
    {
        if(null === $this->container) {
            throw new \RuntimeException("Main container is not set. Use " . __CLASS__ . "::setContainer() to set it.");
        }
    }

    private function assertMandatoryConfigExists(string $path): void
    {
        if(null === $this->config->mixedValue($path)) {
            throw  new \RuntimeException("Missing application config for $path");
        }
    }
}
