<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;

final readonly class OAuthClaims extends AbstractValueObject {
  public function __construct(
    private OAuthSubject $subject,
    private ?Email $email,
    private DisplayName $displayName,
    private bool $emailVerified,
  ) {}

  public function getSubject(): OAuthSubject {
    return $this->subject;
  }

  public function getEmail(): ?Email {
    return $this->email;
  }

  public function getDisplayName(): DisplayName {
    return $this->displayName;
  }

  public function isEmailVerified(): bool {
    return $this->emailVerified;
  }
}
