<?php

declare(strict_types=1);

namespace FlightHub\Application;

interface LockManager
{
    public function acquireLock(Uuid $lockable, Uuid $owner): void;

    public function releaseLock(Uuid $lockable, Uuid $owner): void;

    public function releaseAllLocks(Uuid $owner): void;
}
