<?php
declare(strict_types=1);


namespace FlightHub\Application;


interface LockManager
{
    public function lock(string $lockId, int $time) : bool;

    public function unlock(string $lockId): void;

    public function isLocked(string $lockId) : bool;
}
