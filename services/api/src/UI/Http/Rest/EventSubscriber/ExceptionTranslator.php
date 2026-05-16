<?php

declare(strict_types=1);

namespace UI\Http\Rest\EventSubscriber;

use Slink\Shared\Domain\Exception\ForbiddenException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 16)]
final readonly class ExceptionTranslator {
  public function __invoke(ExceptionEvent $event): void {
    $exception = $event->getThrowable();

    if ($exception instanceof AccessDeniedException) {
      $event->setThrowable(new ForbiddenException(previous: $exception));
    }
  }
}
