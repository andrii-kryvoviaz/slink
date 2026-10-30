<?php

declare(strict_types=1);

namespace Slink\User\Domain\Service;

use Slink\User\Domain\Exception\InvalidPasswordException;

final class BcryptHasher {
  private const int COST = 12;
  private const int TEST_COST = 4;
  private const int MIN_LENGTH = 6;

  public static function hash(#[\SensitiveParameter] string $plainPassword): string {
    if (\mb_strlen($plainPassword) < self::MIN_LENGTH) {
      throw new InvalidPasswordException('Min 6 characters password');
    }

    return \password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => self::cost()]);
  }

  private static function cost(): int {
    if (($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? null) === 'test') {
      return self::TEST_COST;
    }

    return self::COST;
  }
}
