<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Security\Voter;

use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class LocalAuthVoter extends Voter {
  public const LOCAL_AUTH_ENABLED = 'LOCAL_AUTH_ENABLED';

  /**
   * @param ConfigurationProviderInterface<SettingsService> $configurationProvider
   */
  public function __construct(
    private readonly ConfigurationProviderInterface $configurationProvider,
  ) {
  }

  protected function supports(string $attribute, mixed $subject): bool {
    return $attribute === self::LOCAL_AUTH_ENABLED;
  }

  protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool {
    return (bool) $this->configurationProvider->get('user.localAuthEnabled');
  }
}
