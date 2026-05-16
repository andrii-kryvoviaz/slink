<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Security\Voter;

use Slink\Image\Domain\Enum\ImageAccess;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\ValueObject\ImageAccessContext;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Share\Application\Service\ShareAccessGuard;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Domain\ValueObject\TargetPath;
use Slink\Shared\Application\Security\Viewer;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ImageVoter extends Voter {
  /**
   * @param ConfigurationProviderInterface<SettingsService> $configurationProvider
   */
  public function __construct(
    private readonly ShareRepositoryInterface $shareRepository,
    private readonly ShareAccessGuard $accessGuard,
    private readonly ConfigurationProviderInterface $configurationProvider,
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

    if (Viewer::fromToken($token)->owns($image)) {
      return true;
    }

    if ($attribute === ImageAccess::View) {
      $targetPath = $this->targetPathFrom($subject);

      if ($targetPath === null) {
        return false;
      }

      if ($token->getUser() === null && $this->configurationProvider->get('access.requireAuthForMediaShares')) {
        return false;
      }

      return $this->hasAccessibleShare($targetPath);
    }

    return false;
  }

  private function unwrap(mixed $subject): mixed {
    if ($subject instanceof ImageAccessContext) {
      return $subject->image;
    }

    return $subject;
  }

  private function hasAccessibleShare(TargetPath $targetPath): bool {
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
