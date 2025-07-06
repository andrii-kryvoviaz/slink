<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class GuestUser extends AbstractValueObject {
  private function __construct(private DisplayName $displayName) {
  }

  public static function create(): self {
    return new self(DisplayName::fromNullableString(null));
  }

  #[Groups(['public'])]
  #[SerializedName('id')]
  public function getId(): string {
    return 'guest';
  }

  #[Groups(['public'])]
  #[SerializedName('displayName')]
  public function getDisplayName(): string {
    return $this->displayName->toString();
  }

  #[Groups(['public'])]
  #[SerializedName('username')]
  public function getUsername(): string {
    return 'guest';
  }

  #[Groups(['public'])]
  #[SerializedName('email')]
  public function getEmail(): string {
    return 'guest@localhost';
  }

  public function toString(): string {
    return $this->displayName->toString();
  }
}
