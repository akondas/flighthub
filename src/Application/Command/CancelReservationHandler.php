<?php

declare(strict_types=1);

namespace FlightHub\Application\Command;

use FlightHub\Application\LockManager;
use FlightHub\Application\TransactionManager;

final class CancelReservationHandler
{
    /**
     * @var LockManager
     */
    private $lockManager;

    private $flights;

    public function handle(CancelReservation $command): void
    {
        $this->lockManager->lock($command->reservationId(), 60);

        try {
            $flight = $this->flights->getByReservationId($command->reservationId());
            $flight->cancelReservation($command->reservationId());
            $this->paymentService->returnFunds($command->reservationId());

        } catch (\Throwable $exception) {
            $this->lockManager->unlock($command->reservationId());

            throw $exception;
        }

        $this->lockManager->unlock($command->reservationId());
    }
}
