<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\Security\Voter;

use Slink\Share\Domain\Enum\ShareAccess;
use Slink\Share\Domain\Service\ShareableOwnerResolverInterface;
use Slink\Share\Domain\Share;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ShareVoter extends Voter {
  public function __construct(
    private readonly ShareableOwnerResolverInterface $ownerResolver,
  ) {}

  protected function supports(mixed $attribute, mixed $subject): bool {
    if (!$attribute instanceof ShareAccess) {
      return false;
    }

    if ($subject instanceof ShareView) {
      return true;
    }

    if ($subject instanceof Share) {
      return true;
    }

    return false;
  }

  protected function voteOnAttribute(mixed $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool {
    return $this->isOwner($subject, $token);
  }

  private function isOwner(mixed $subject, TokenInterface $token): bool {
    $ownerId = $this->resolveOwnerId($subject);

    if ($ownerId === null) {
      return false;
    }

    $userIdentifier = $token->getUserIdentifier();

    if ($userIdentifier === '') {
      return false;
    }

    return ID::fromString($ownerId)->equals(ID::fromString($userIdentifier));
  }

  private function resolveOwnerId(mixed $subject): ?string {
    $shareable = $this->extractShareable($subject);

    if ($shareable === null) {
      return null;
    }

    return $this->ownerResolver->resolveOwnerId($shareable);
  }

  private function extractShareable(mixed $subject): ?ShareableReference {
    if ($subject instanceof ShareView) {
      return $subject->getShareable();
    }

    if ($subject instanceof Share) {
      return $subject->getShareable();
    }

    return null;
  }
}
