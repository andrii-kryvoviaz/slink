<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\Auth;

use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;

final readonly class Credentials {
  /**
   * @param Email $email
   * @param Username $username
   * @param HashedPassword $password
   */
  public function __construct(
    public Email $email,
    public Username $username,
    public HashedPassword $password
  ) {
  }
  
  /**
   * @param Email $email
   * @param Username $username
   * @param HashedPassword $password
   * @return static
   */
  public static function create(
    Email $email,
    Username $username,
    HashedPassword $password
  ): static {
    return new self($email, $username, $password);
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  public static function fromPayload(array $payload): static {
    return new self(
      Email::fromString($payload['email']),
      Username::fromString($payload['username']),
      HashedPassword::fromHash($payload['password'])
    );
  }
}