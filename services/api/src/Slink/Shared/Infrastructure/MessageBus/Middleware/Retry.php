<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\MessageBus\Middleware;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final readonly class Retry implements MiddlewareInterface {
  private const int ATTEMPTS = 2;

  public function __construct(private ManagerRegistry $registry) {
  }

  public function handle(Envelope $envelope, StackInterface $stack): Envelope {
    return $this->attempt($envelope, $stack, self::ATTEMPTS);
  }

  private function attempt(Envelope $envelope, StackInterface $stack, int $left): Envelope {
    $rest = clone $stack;

    try {
      return $stack->next()->handle($envelope, $stack);
    } catch (\Throwable $failure) {
      if ($left === 1 || !$this->conflicts($failure)) {
        throw $failure;
      }

      $this->registry->resetManager();

      return $this->attempt($envelope, $rest, $left - 1);
    }
  }

  private function conflicts(\Throwable $failure): bool {
    if ($failure instanceof HandlerFailedException) {
      return $failure->getWrappedExceptions(UniqueConstraintViolationException::class, true) !== [];
    }

    return $failure instanceof UniqueConstraintViolationException;
  }
}
