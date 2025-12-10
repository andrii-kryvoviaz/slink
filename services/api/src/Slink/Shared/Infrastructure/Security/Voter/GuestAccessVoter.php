<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Security\Voter;

use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class GuestAccessVoter extends Voter {
  public const GUEST_UPLOAD_ALLOWED = 'GUEST_UPLOAD_ALLOWED';
  public const GUEST_VIEW_ALLOWED = 'GUEST_VIEW_ALLOWED';
  
  /**
   * @param ConfigurationProviderInterface<SettingsService> $configurationProvider
   */
  public function __construct(
    private readonly ConfigurationProviderInterface $configurationProvider,
  ) {
  }
  
  /**
   * @param string $attribute
   * @param mixed $subject
   * @return bool
   */
  protected function supports(string $attribute, mixed $subject): bool {
    return in_array($attribute, [
      self::GUEST_UPLOAD_ALLOWED,
      self::GUEST_VIEW_ALLOWED
    ]);
  }
  
  /**
   * @param string $attribute
   * @param mixed $subject
   * @param TokenInterface $token
   * @return bool
   */
  protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool {
    if ($token->getUser() !== null) {
      return true;
    }
    
    return match ($attribute) {
      self::GUEST_UPLOAD_ALLOWED => $this->configurationProvider->get('access.allowGuestUploads'),
      self::GUEST_VIEW_ALLOWED => $this->configurationProvider->get('access.allowUnauthenticatedAccess'),

      default => false
    };
  }
}
