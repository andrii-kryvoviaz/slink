<?php

declare(strict_types=1);

namespace Slink\Tag\Infrastructure\Security\Voter;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Enum\TagAccess;
use Slink\Tag\Domain\Tag;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class TagVoter extends Voter {
  /**
   * @param mixed $attribute
   */
  protected function supports(mixed $attribute, mixed $subject): bool {
    if (!$attribute instanceof TagAccess) {
      return false;
    }

    if ($subject instanceof TagView) {
      return true;
    }

    if ($subject instanceof Tag) {
      return true;
    }

    return false;
  }

  /**
   * @param mixed $attribute
   */
  protected function voteOnAttribute(mixed $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool {
    if ($this->extractOwnerId($subject) === null) {
      return false;
    }

    return $this->isOwner($subject, $token);
  }

  private function isOwner(mixed $tag, TokenInterface $token): bool {
    $ownerId = $this->extractOwnerId($tag);

    if ($ownerId === null) {
      return false;
    }

    $userIdentifier = $token->getUserIdentifier();

    if ($userIdentifier === '') {
      return false;
    }

    return ID::fromString($ownerId)->equals(ID::fromString($userIdentifier));
  }

  private function extractOwnerId(mixed $tag): ?string {
    if ($tag instanceof TagView) {
      return $tag->getUserId();
    }

    if ($tag instanceof Tag) {
      return $tag->getUserId()->toString();
    }

    return null;
  }
}
