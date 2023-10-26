<?php

declare(strict_types=1);

namespace Slik\User\Domain\ValueObject\Auth;

use SensitiveParameter;
use Slik\User\Domain\ValueObject\Email;

final readonly class Credentials {
  public function __construct(public Email $email, public HashedPassword $password) {
  }

  public static function fromString(string $email,#[SensitiveParameter] string $password): static {
    return new self(Email::fromString($email), HashedPassword::encode($password));
  }
}