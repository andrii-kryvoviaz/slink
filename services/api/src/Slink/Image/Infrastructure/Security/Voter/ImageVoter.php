<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Security\Voter;

use Slink\Image\Domain\Enum\ImageAccess;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\ValueObject\ImageAccessContext;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Share\Application\Service\ShareAccessGuard;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Domain\ValueObject\TargetPath;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ImageVoter extends Voter {
  public function __construct(
    private readonly ShareRepositoryInterface $shareRepository,
    private readonly ShareAccessGuard $accessGuard,
  ) {}

  /**
   * @param mixed $attribute
   */
  protected function supports(mixed $attribute, mixed $subject): bool {
    if (!$attribute instanceof ImageAccess) {
      return false;
    }

    if ($attribute === ImageAccess::Tag) {
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

      return $this->hasAccessibleShareForUrl($this->targetPathFrom($subject));
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
    if (!$image instanceof ImageView && !$image instanceof Image) {
      return false;
    }

    $ownerId = $image->getUserId();

    if ($ownerId === null) {
      return false;
    }

    $userIdentifier = $token->getUserIdentifier();

    if ($userIdentifier === '') {
      return false;
    }

    return $ownerId->equals(ID::fromString($userIdentifier));
  }

  private function hasAccessibleShareForUrl(?TargetPath $targetPath): bool {
    if ($targetPath === null) {
      return false;
    }

    $share = $this->shareRepository->findByTargetPath($targetPath);

    if ($share === null) {
      return false;
    }

    return $this->accessGuard->allows($share);
  }

  private function targetPathFrom(mixed $subject): ?TargetPath {
    if ($subject instanceof ImageAccessContext) {
      return $subject->targetPath;
    }

    return null;
  }
}
