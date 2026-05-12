<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\Security\Voter;

use Slink\Collection\Domain\Collection;
use Slink\Collection\Domain\Enum\CollectionAccess;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;
use Slink\Share\Application\Service\ShareAccessGuard;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Shared\Application\Security\Viewer;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class CollectionVoter extends Voter {
  public function __construct(
    private readonly ShareRepositoryInterface $shareRepository,
    private readonly ShareAccessGuard $accessGuard,
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

    if (Viewer::fromToken($token)->owns($subject)) {
      return true;
    }

    if ($attribute === CollectionAccess::View) {
      return $this->hasAccessibleShare($collectionId);
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

  private function hasAccessibleShare(string $collectionId): bool {
    $share = $this->shareRepository->findByShareable($collectionId, ShareableType::Collection);

    if ($share === null) {
      return false;
    }

    return $this->accessGuard->allows($share);
  }
}
