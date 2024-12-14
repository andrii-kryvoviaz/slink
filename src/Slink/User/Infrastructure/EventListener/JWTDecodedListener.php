<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Slink\User\Application\Service\UserRoleManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class JWTDecodedListener {
  
  public function __construct(
    private UserRoleManagerInterface $userRoleManager
  ) {}
  
  #[AsEventListener(event: 'lexik_jwt_authentication.on_jwt_decoded')]
  public function onTokenDecoded(JWTDecodedEvent $event): void
  {
    ['iat' => $iat, 'uuid' => $uuid] = $event->getPayload();
    
    if ($iat === null) {
      return;
    }
    
    $permissionsVersion = $this->userRoleManager->getPermissionsVersion($uuid);
    
    if ($permissionsVersion === null) {
      return;
    }
    
    if ($permissionsVersion > $iat) {
      $event->markAsInvalid();
    }
  }
}