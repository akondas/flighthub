<?php

declare(strict_types=1);

namespace FlightHub\Infrastructure\LockManager;

use FlightHub\Application\LockManager;
use Prooph\EventStore\Exception\ConcurrencyException;

final class SharedReadLockManager implements LockManager
{
    /**
     * @var \PDO
     */
    private $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function acquireLock(Uuid $lockable, Uuid $owner): void
    {
        if($this->hasLock($lockable, $owner)) {
            return;
        }

        $stm = $this->connection->prepare(
            'INSERT INTO read_locks WHERE lockable = :lockable AND owner = :owner'
        );

        try {
            $stm->execute([':lockable' => $lockable, ':owner' => $owner]);
        } catch (\PDOException $exception) {
            throw new ConcurrencyException(sprintf('Can\'t get a lock for %s with owner %s', $lockable, $owner));
        }
    }

    public function releaseLock(Uuid $lockable, Uuid $owner): void
    {
        $stm = $this->connection->prepare(
            'DELETE FROM read_locks WHERE lockable = :lockable AND owner = :owner'
        );

        try {
            $stm->execute([':lockable' => $lockable, ':owner' => $owner]);
        } catch (\PDOException $exception) {
            throw new ConcurrencyException(sprintf(
                'Can\'t release lock for %s with owner %s',
                $lockable,
                $owner
            ));
        }
    }

    public function releaseAllLocks(Uuid $owner): void
    {
        $stm = $this->connection->prepare(
            'DELETE FROM read_locks WHERE owner = :owner'
        );

        try {
            $stm->execute([':owner' => $owner]);
        } catch (\PDOException $exception) {
            throw new ConcurrencyException(sprintf(
                'Can\'t release lock for owner %s',
                $owner
            ));
        }
    }

    private function hasLock(Uuid $lockable, Uuid $owner): void
    {

    }
}
