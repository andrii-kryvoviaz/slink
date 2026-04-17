<?php

declare(strict_types=1);

namespace Slink\Share\Domain\AccessRule;

use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\Date\DateTime;

final readonly class NotExpiredRule implements ShareAccessRule {
  public function supports(object $subject): bool {
    return $subject instanceof ExpirationAware;
  }

  public function allows(ExpirationAware $subject): bool {
    try {
      $expiresAt = $subject->getExpiresAt();

      if ($expiresAt === null) {
        return true;
      }

      return $expiresAt->isAfter(DateTime::now());
    } catch (DateTimeException) {
      return false;
    }
  }
}
