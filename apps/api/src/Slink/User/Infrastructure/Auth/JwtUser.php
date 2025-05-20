<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use Slink\User\Domain\Contracts\UserInterface as User;

final readonly class JwtUser implements User, JWTUserInterface {
  
  /**
   * @param string $identifier
   * @param array<string> $roles
   */
  private function __construct(
    private string $identifier,
    private array $roles
  ) {}
  
  /**
   * @param User $user
   * @return self
   */
  public static function createFromUser(User $user): self {
    return new self($user->getIdentifier(), $user->getRoles());
  }
  
  /**
   * @param $username
   * @param array<string, mixed> $payload
   * @return JWTUserInterface|self
   */
  #[\Override]
  public static function createFromPayload($username, array $payload): JwtUser|JWTUserInterface {
    return new self($username, $payload['roles']);
  }
  
  /**
   * @return string
   */
  public function getUserIdentifier(): string {
    return $this->identifier;
  }
  
  /**
   * @return array<string>
   */
  public function getRoles(): array {
    return $this->roles;
  }
  
  /**
   * @return void
   */
  public function eraseCredentials(): void {
  }
  
  #[\Override]
  public function getIdentifier(): string {
    return $this->getUserIdentifier();
  }
}