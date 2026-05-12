<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Security;

use Slink\Shared\Domain\Contract\OwnerAwareInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final readonly class Viewer {
  public function __construct(
    public ?ID $userId = null,
  ) {}

  public static function fromToken(TokenInterface $token): self {
    return self::fromIdentifier($token->getUserIdentifier());
  }

  public static function fromIdentifier(?string $userIdentifier): self {
    return new self(ID::fromUnknown($userIdentifier));
  }

  public function isAnonymous(): bool {
    return $this->userId === null;
  }

  public function owns(mixed $subject): bool {
    return $subject instanceof OwnerAwareInterface
      && $subject->isOwnedBy($this->userId);
  }
}
