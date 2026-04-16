<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\Security\Voter;

use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Image\Domain\Enum\ImageAccess;
use Slink\Image\Domain\Service\ImageUrlSignatureInterface;
use Slink\Image\Domain\ValueObject\ImageAccessContext;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class CollectionScopedImageVoter extends Voter {
  public function __construct(
    private readonly ImageUrlSignatureInterface $signature,
    private readonly ShareRepositoryInterface $shareRepository,
    private readonly CollectionItemRepositoryInterface $collectionItemRepository,
  ) {}

  /**
   * @param mixed $attribute
   */
  protected function supports(mixed $attribute, mixed $subject): bool {
    if ($attribute !== ImageAccess::View) {
      return false;
    }

    if (!$subject instanceof ImageAccessContext) {
      return false;
    }

    return true;
  }

  /**
   * @param mixed $attribute
   */
  protected function voteOnAttribute(mixed $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool {
    if (!$subject instanceof ImageAccessContext) {
      return false;
    }

    $collectionId = $subject->scopeCollectionId;

    if ($collectionId === null) {
      return false;
    }

    if ($collectionId === '') {
      return false;
    }

    $signature = $subject->scopeSignature;

    if ($signature === null) {
      return false;
    }

    if ($signature === '') {
      return false;
    }

    $imageId = $subject->image->getUuid();

    if (!$this->signature->verify($imageId, ['collection' => $collectionId], $signature)) {
      return false;
    }

    $share = $this->shareRepository->findByShareable($collectionId, ShareableType::Collection);

    if ($share === null) {
      return false;
    }

    if (!$share->isPublished()) {
      return false;
    }

    $item = $this->collectionItemRepository->findByCollectionAndItemId($collectionId, $imageId);

    if ($item === null) {
      return false;
    }

    return true;
  }
}
