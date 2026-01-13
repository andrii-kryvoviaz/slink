<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\EventListener;

use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;
use Slink\Shared\Infrastructure\Encryption\EncryptionService;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class EncryptionBootstrapSubscriber implements EventSubscriberInterface {
  public function __construct(
    private EncryptionService $encryptionService
  ) {
  }
  
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::REQUEST => ['bootstrap', 255],
      ConsoleEvents::COMMAND => ['bootstrap', 255],
    ];
  }
  
  public function bootstrap(): void {
    EncryptionRegistry::setService($this->encryptionService);
  }
}
