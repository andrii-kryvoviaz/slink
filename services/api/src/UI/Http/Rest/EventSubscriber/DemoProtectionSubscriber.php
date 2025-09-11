<?php

declare(strict_types=1);

namespace UI\Http\Rest\EventSubscriber;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\Exception\DemoForbiddenException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class DemoProtectionSubscriber implements EventSubscriberInterface {
  
  private const array FORBIDDEN_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];
  private const array ALLOWED_ROUTES = ['/api/auth/login'];
  
  public function __construct(
    private ConfigurationProviderInterface $configurationProvider
  ) {
  }
  
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::REQUEST => ['onKernelRequest', 10],
    ];
  }
  
  public function onKernelRequest(RequestEvent $event): void {
    if (!$this->configurationProvider->get('demo.enabled')) {
      return;
    }
    
    $request = $event->getRequest();
    $method = $request->getMethod();
    $path = $request->getPathInfo();
    
    if (in_array($method, self::FORBIDDEN_METHODS, true) && !in_array($path, self::ALLOWED_ROUTES, true)) {
      throw new DemoForbiddenException();
    }
  }
}
