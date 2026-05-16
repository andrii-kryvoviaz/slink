<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\Security\Voter;

use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Share\Domain\Enum\ShareAccess;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Service\ShareableOwnerResolverInterface;
use Slink\Share\Domain\Share;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ShareUnlockVoter extends Voter {
  /**
   * @param ConfigurationProviderInterface<SettingsService> $configurationProvider
   */
  public function __construct(
    private readonly ShareableOwnerResolverInterface $ownerResolver,
    private readonly ConfigurationProviderInterface $configurationProvider,
  ) {}

  protected function supports(mixed $attribute, mixed $subject): bool {
    if ($attribute !== ShareAccess::Unlock) {
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
    if ($this->isOwner($subject, $token)) {
      return true;
    }

    if ($token->getUser() !== null) {
      return true;
    }

    $shareable = $this->extractShareable($subject);

    if ($shareable === null) {
      return false;
    }

    return match ($shareable->getShareableType()) {
      ShareableType::Image => !$this->configurationProvider->get('access.requireAuthForMediaShares'),
      ShareableType::Collection => !$this->configurationProvider->get('access.requireAuthForCollectionShares'),
    };
  }

  private function isOwner(mixed $subject, TokenInterface $token): bool {
    $userIdentifier = $token->getUserIdentifier();

    if ($userIdentifier === '') {
      return false;
    }

    $shareable = $this->extractShareable($subject);

    if ($shareable === null) {
      return false;
    }

    $ownerId = $this->ownerResolver->resolveOwnerId($shareable);

    if ($ownerId === null) {
      return false;
    }

    return ID::fromString($ownerId)->equals(ID::fromString($userIdentifier));
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
