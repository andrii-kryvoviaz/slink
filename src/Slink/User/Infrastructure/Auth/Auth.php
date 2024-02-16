<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth;

use Slink\User\Domain\Contracts\UserInterface as User;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class Auth implements UserInterface {
  
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
}