<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\Security\Voter;

use Slink\Collection\Domain\Enum\CollectionScopedImageAccess;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Domain\ValueObject\CollectionScopedImageAccessContext;
use Slink\Share\Application\Service\ShareAccessGuard;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class CollectionScopedImageVoter extends Voter {
  public function __construct(
    private readonly ShareRepositoryInterface $shareRepository,
    private readonly CollectionItemRepositoryInterface $collectionItemRepository,
    private readonly ShareAccessGuard $accessGuard,
  ) {}

  /**
   * @param mixed $attribute
   */
  protected function supports(mixed $attribute, mixed $subject): bool {
    if (!$attribute instanceof CollectionScopedImageAccess) {
      return false;
    }

    return $subject instanceof CollectionScopedImageAccessContext;
  }

  /**
   * @param mixed $attribute
   */
  protected function voteOnAttribute(mixed $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool {
    if (!$subject instanceof CollectionScopedImageAccessContext) {
      return false;
    }

    $share = $this->shareRepository->findByShareable($subject->collectionId, ShareableType::Collection);

    if ($share === null) {
      return false;
    }

    if (!$this->accessGuard->allows($share)) {
      return false;
    }

    return $this->collectionItemRepository->findByCollectionAndItemId(
      $subject->collectionId,
      $subject->itemId,
    ) !== null;
  }
}
