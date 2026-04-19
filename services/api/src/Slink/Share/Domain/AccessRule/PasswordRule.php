<?php

declare(strict_types=1);

namespace Slink\Share\Domain\AccessRule;

use Slink\Share\Domain\Exception\SharePasswordRequiredException;
use Slink\Share\Domain\Service\ShareUnlockVerifierInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class PasswordRule implements ShareAccessRule {
  public function __construct(
    private ShareUnlockVerifierInterface $unlockVerifier,
  ) {}

  public function supports(object $subject): bool {
    return $subject instanceof PasswordProtected;
  }

  public function allows(PasswordProtected $subject): bool {
    if ($subject->getPassword() === null) {
      return true;
    }

    if ($this->unlockVerifier->isVerified(ID::fromString($subject->getId()))) {
      return true;
    }

    throw new SharePasswordRequiredException($subject->getId());
  }
}
