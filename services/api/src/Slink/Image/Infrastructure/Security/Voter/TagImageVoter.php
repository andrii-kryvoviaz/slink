<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Security\Voter;

use Slink\Image\Domain\Enum\ImageAccess;
use Slink\Image\Domain\Image;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class TagImageVoter extends Voter {
  /**
   * @param mixed $attribute
   */
  protected function supports(mixed $attribute, mixed $subject): bool {
    if ($attribute !== ImageAccess::Tag) {
      return false;
    }

    if ($subject instanceof ImageView) {
      return true;
    }

    if ($subject instanceof Image) {
      return true;
    }

    return false;
  }

  /**
   * @param mixed $attribute
   */
  protected function voteOnAttribute(mixed $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool {
    if (!$subject instanceof ImageView && !$subject instanceof Image) {
      return false;
    }

    $ownerId = $subject->getUserId();

    if ($ownerId === null) {
      return true;
    }

    $userIdentifier = $token->getUserIdentifier();

    if ($userIdentifier === '') {
      return false;
    }

    return $ownerId->equals(ID::fromString($userIdentifier));
  }
}
