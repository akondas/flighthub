<?php
declare(strict_types = 1);

namespace FlightHub\Config;

use FlightHub\Application\Aggregate;
use FlightHub\Application\Command;
use FlightHub\Application\Event;
use FlightHub\Application\FlightDescription;
use FlightHub\Application\Handler;
use FlightHub\Application\Listener;
use FlightHub\Application\MessageDescription;
use FlightHub\Application\Projection;
use FlightHub\Application\Query;
use FlightHub\Application\Type;

return [
    'environment' => getenv('PROOPH_ENV')?: 'prod',
    'pdo' => [
        'dsn' => getenv('PDO_DSN'),
        'user' => getenv('PDO_USER'),
        'pwd' => getenv('PDO_PWD'),
    ],
    'rabbit' => [
        'connection' => [
            'host' => getenv('RABBIT_HOST')?: 'rabbit',
            'port' => (int)getenv('RABBIT_PORT')?: 5672,
            'login' => getenv('RABBIT_USER')?: 'event-machine',
            'password' => getenv('RABBIT_PWD')?: 'event-machine',
            'vhost' => getenv('RABBIT_VHOST')?: '/event-machine',
            'persistent' => (bool)getenv('RABBIT_PERSISTENT')?: false,
            'read_timeout' => (int)getenv('RABBIT_READ_TIMEOUT')?: 1, //sec, float allowed
            'write_timeout' => (int)getenv('RABBIT_WRITE_TIMEOUT')?: 1, //sec, float allowed,
            'heartbeat' => (int)getenv('RABBIT_HEARTBEAT')?: 0,
            'verify' => false
        ],
        'ui_exchange' => getenv('RABBIT_UI_EXCHANGE')?: 'ui-exchange',
    ],
    'event_machine' => [
        'descriptions' => [
            MessageDescription::class,
            FlightDescription::class
        ]
    ]
];
