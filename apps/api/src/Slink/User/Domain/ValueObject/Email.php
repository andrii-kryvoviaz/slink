<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\User\Domain\Exception\InvalidEmailException;

final readonly class Email extends AbstractValueObject{
  private function __construct(private string $email) {
    self::validate($email);
  }

  public static function fromString(string $email): self {
    return new self($email);
  }
  
  public static function fromStringOrNull(?string $email): ?self {
    if ($email === null) {
      return null;
    }
    
    try {
      return new self($email);
    } catch (InvalidEmailException) {
      return null;
    }
  }

  public function toString(): string {
    return $this->email ?? '';
  }

  private static function validate(string $email): void {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      throw new InvalidEmailException('Invalid email address.');
    }
  }
}