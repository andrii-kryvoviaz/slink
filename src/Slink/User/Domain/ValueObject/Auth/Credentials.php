<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\Auth;

use SensitiveParameter;
use Slink\User\Domain\ValueObject\Email;

final readonly class Credentials {
  /**
   * @param Email $email
   * @param HashedPassword $password
   */
  public function __construct(public Email $email, public HashedPassword $password) {
  }
  
  /**
   * @param string $email
   * @param string $password
   * @return static
   */
  public static function fromPlainCredentials(string $email, #[SensitiveParameter] string $password): static {
    return new self(
      Email::fromString($email),
      HashedPassword::encode($password)
    );
  }
  
  /**
   * @param Email $email
   * @param HashedPassword $password
   * @return static
   */
  public static function fromCredentials(Email $email, HashedPassword $password): static {
    return new self($email, $password);
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  public static function fromPayload(array $payload): static {
    return new self(
      Email::fromString($payload['email']),
      HashedPassword::fromHash($payload['password'])
    );
  }
}