<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\Security\Voter;

use Slink\Collection\Domain\Collection;
use Slink\Collection\Domain\Enum\CollectionAccess;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class CollectionVoter extends Voter {
  public function __construct(
    private readonly ShareRepositoryInterface $shareRepository,
  ) {}

  /**
   * @param mixed $attribute
   */
  protected function supports(mixed $attribute, mixed $subject): bool {
    if (!$attribute instanceof CollectionAccess) {
      return false;
    }

    if ($subject instanceof CollectionView) {
      return true;
    }

    if ($subject instanceof Collection) {
      return true;
    }

    return false;
  }

  /**
   * @param mixed $attribute
   */
  protected function voteOnAttribute(mixed $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool {
    $collectionId = $this->extractCollectionId($subject);

    if ($collectionId === null) {
      return false;
    }

    if ($this->isOwner($subject, $token)) {
      return true;
    }

    if ($attribute === CollectionAccess::View) {
      return $this->hasPublishedShare($collectionId);
    }

    return false;
  }

  private function extractCollectionId(mixed $subject): ?string {
    if ($subject instanceof CollectionView) {
      return $subject->getId();
    }

    if ($subject instanceof Collection) {
      return $subject->aggregateRootId()->toString();
    }

    return null;
  }

  private function isOwner(mixed $subject, TokenInterface $token): bool {
    $ownerId = $this->extractOwnerId($subject);

    if ($ownerId === null) {
      return false;
    }

    $userIdentifier = $token->getUserIdentifier();

    if ($userIdentifier === '') {
      return false;
    }

    return ID::fromString($ownerId)->equals(ID::fromString($userIdentifier));
  }

  private function extractOwnerId(mixed $subject): ?string {
    if ($subject instanceof CollectionView) {
      return $subject->getUserId();
    }

    if ($subject instanceof Collection) {
      return $subject->getUserId()->toString();
    }

    return null;
  }

  private function hasPublishedShare(string $collectionId): bool {
    $share = $this->shareRepository->findByShareable($collectionId, ShareableType::Collection);

    if ($share === null) {
      return false;
    }

    return $share->isPublished();
  }
}
