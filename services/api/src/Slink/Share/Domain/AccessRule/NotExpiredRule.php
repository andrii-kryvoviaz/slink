<?php

declare(strict_types=1);

namespace Slink\Share\Domain\AccessRule;

use Slink\Share\Domain\Exception\ShareExpiredException;
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

      if ($expiresAt->isAfter(DateTime::now())) {
        return true;
      }
    } catch (DateTimeException) {
      throw new ShareExpiredException();
    }

    throw new ShareExpiredException();
  }
}
