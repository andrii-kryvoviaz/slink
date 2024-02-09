<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth;

use Ramsey\Uuid\UuidInterface;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\Email;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherAwareInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class Auth implements UserInterface, PasswordHasherAwareInterface, PasswordAuthenticatedUserInterface, \Stringable {
  
  private function __construct(
    private UuidInterface $uuid,
    private Email $email,
    private HashedPassword $hashedPassword
  ) {}
  
  public static function create(UuidInterface $uuid, Email $email, HashedPassword $hashedPassword): self {
    return new self($uuid, $email, $hashedPassword);
  }
  
  public function getPassword(): ?string {
    return $this->hashedPassword->toString();
  }
  
  public function getPasswordHasherName(): ?string {
    return 'hasher';
  }
  
  public function __toString(): string {
    return $this->email->toString();
  }
  
  public function getRoles(): array {
    return ['ROLE_USER'];
  }
  
  public function eraseCredentials(): void {
  }
  
  public function getUserIdentifier(): string {
    return $this->email->toString();
  }
  
  public function uuid(): UuidInterface {
    return $this->uuid;
  }
}