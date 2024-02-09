<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class Email extends AbstractValueObject{
  private function __construct(private string $email) {
  }

  public static function fromString(string $email): self {
    self::validate($email);

    return new self($email);
  }

  public function toString(): string {
    return $this->email ?? '';
  }

  private static function validate(string $email): void {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      throw new \InvalidArgumentException('Invalid email address');
    }
  }
}