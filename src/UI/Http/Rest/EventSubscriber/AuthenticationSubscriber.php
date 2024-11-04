<?php

declare(strict_types=1);

namespace UI\Http\Rest\EventSubscriber;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final readonly class AuthenticationSubscriber implements EventSubscriberInterface {
  public function __construct(
    private ConfigurationProviderInterface $configurationProvider,
    private Security                       $security,
    private TokenStorageInterface          $tokenStorage,
  ) {
  }
  
  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::REQUEST => 'onKernelRequest'
    ];
  }
  
  /**
   * @throws \Exception
   */
  public function onKernelRequest(RequestEvent $event): void {
    if($this->configurationProvider->get('user.allowUnauthenticatedAccess')) {
      return;
    }
    
    $request = $event->getRequest();
    $firewallConfig = $this->security->getFirewallConfig($request);
    
    if(!$firewallConfig || $firewallConfig->getName() !== 'api') {
      return;
    }
    
    if(!$this->tokenStorage->getToken()?->getUser()) {
      throw new AuthenticationException();
    }
  }
}