<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\PayloadAwareUserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Service\ResetInterface;

final class JwtUserProvider implements PayloadAwareUserProviderInterface, ResetInterface
{
  /**
   * @var array<string, UserInterface>
   */
  private array $cache = [];

  /**
   * @param array<string, mixed> $payload
   */
  public function loadUserByIdentifier(string $identifier, array $payload = []): UserInterface
  {
    return $this->loadUserByIdentifierAndPayload($identifier, $payload);
  }
  
  /**
   * @param string $identifier
   * @param array<string, mixed> $payload
   * @return UserInterface
   */
  public function loadUserByIdentifierAndPayload(string $identifier, array $payload): UserInterface
  {
    if (isset($this->cache[$identifier])) {
      return $this->cache[$identifier];
    }

    return $this->cache[$identifier] = JwtUser::createFromPayload($identifier, $payload);
  }
  
  /**
   * {@inheritdoc}
   */
  public function supportsClass(string $class): bool
  {
    return $class === JwtUser::class;
  }

  public function refreshUser(UserInterface $user): UserInterface
  {
    return $user;
  }
  
  /**
   * @return void
   */
  public function reset(): void {
    $this->cache = [];
  }
}