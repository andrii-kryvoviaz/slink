<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;

final readonly class OAuthIdentity extends AbstractValueObject {
  public function __construct(
    private OAuthSubject $subject,
    private ?Email $email,
    private DisplayName $displayName,
    private bool $emailVerified,
  ) {}

  public static function fromTokenClaims(TokenClaims $claims, OAuthProvider $provider): self {
    $sub = $claims->getSubject();
    $email = $claims->getEmail();

    if ($sub === null || $email === null) {
      throw new InvalidCredentialsException();
    }

    return new self(
      OAuthSubject::create($provider, $sub),
      $email,
      $claims->getDisplayName() ?? DisplayName::fromString((string) $email),
      $claims->isEmailVerified(),
    );
  }

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
