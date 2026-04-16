<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Security\Voter;

use Slink\Image\Domain\Enum\ImageAccess;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\ValueObject\ImageAccessContext;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ImageVoter extends Voter {
  public function __construct(
    private readonly ShareRepositoryInterface $shareRepository,
  ) {}

  /**
   * @param mixed $attribute
   */
  protected function supports(mixed $attribute, mixed $subject): bool {
    if (!$attribute instanceof ImageAccess) {
      return false;
    }

    if ($subject instanceof ImageAccessContext) {
      return true;
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
    $image = $this->unwrap($subject);

    $imageId = $this->extractImageId($image);

    if ($imageId === null) {
      return false;
    }

    if ($this->isOwner($image, $token)) {
      return true;
    }

    if ($attribute === ImageAccess::View) {
      if ($this->isPublic($image)) {
        return true;
      }

      return $this->hasPublishedShare($imageId);
    }

    if ($attribute === ImageAccess::Tag) {
      return $this->extractOwnerId($image) === null;
    }

    return false;
  }

  private function isPublic(mixed $image): bool {
    if (!$image instanceof ImageView && !$image instanceof Image) {
      return false;
    }

    return $image->getAttributes()->isPublic();
  }

  private function unwrap(mixed $subject): mixed {
    if ($subject instanceof ImageAccessContext) {
      return $subject->image;
    }

    return $subject;
  }

  private function extractImageId(mixed $image): ?string {
    if ($image instanceof ImageView) {
      return $image->getUuid();
    }

    if ($image instanceof Image) {
      return $image->aggregateRootId()->toString();
    }

    return null;
  }

  private function isOwner(mixed $image, TokenInterface $token): bool {
    $ownerId = $this->extractOwnerId($image);

    if ($ownerId === null) {
      return false;
    }

    $userIdentifier = $token->getUserIdentifier();

    if ($userIdentifier === '') {
      return false;
    }

    return ID::fromString($ownerId)->equals(ID::fromString($userIdentifier));
  }

  private function extractOwnerId(mixed $image): ?string {
    if ($image instanceof ImageView) {
      $user = $image->getUser();

      if ($user === null) {
        return null;
      }

      return $user->getUuid();
    }

    if ($image instanceof Image) {
      $userId = $image->getUserId();

      if ($userId === null) {
        return null;
      }

      return $userId->toString();
    }

    return null;
  }

  private function hasPublishedShare(string $imageId): bool {
    $share = $this->shareRepository->findByShareable($imageId, ShareableType::Image);

    if ($share === null) {
      return false;
    }

    return $share->isPublished();
  }
}
