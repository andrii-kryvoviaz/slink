<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth;

use Slink\User\Domain\Contracts\UserInterface as DomainUserInterface;
use Slink\User\Infrastructure\ReadModel\View\ApiKeyView;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class ApiKeyUser implements DomainUserInterface, UserInterface {
  /**
   * @param array<string> $roles
   */
  private function __construct(
    private string $userId,
    private string $keyId,
    private array $roles = ['ROLE_USER']
  ) {}

  public static function fromApiKey(ApiKeyView $apiKey): self {
    return new self($apiKey->getUserId(), $apiKey->getKeyId());
  }

  public function getIdentifier(): string {
    return $this->userId;
  }

  public function getUserIdentifier(): string {
    assert($this->userId !== '', 'User ID cannot be empty');
    return $this->userId;
  }

  public function getKeyId(): string {
    return $this->keyId;
  }

  public function getRoles(): array {
    return $this->roles;
  }

  public function eraseCredentials(): void {
  }
}
