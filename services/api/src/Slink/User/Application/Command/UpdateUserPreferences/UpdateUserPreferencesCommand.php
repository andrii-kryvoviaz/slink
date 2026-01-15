<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\UpdateUserPreferences;

use Slink\Image\Domain\Enum\License;
use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Slink\User\Domain\Enum\LandingPage;
use Slink\User\Domain\ValueObject\UserPreferences;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateUserPreferencesCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[SerializedName('license.default')]
    #[Assert\Choice(callback: [License::class, 'values'], message: 'Invalid license type.')]
    private ?string $defaultLicense = null,
    #[SerializedName('license.syncToImages')]
    private bool $syncLicenseToImages = false,
    #[SerializedName('navigation.landingPage')]
    #[Assert\Choice(callback: [LandingPage::class, 'values'], message: 'Invalid landing page.')]
    private ?string $defaultLandingPage = null,
  ) {
  }

  public function getPreferences(): UserPreferences {
    return UserPreferences::create(
      defaultLicense: $this->getDefaultLicense(),
      defaultLandingPage: $this->getDefaultLandingPage(),
    );
  }

  /**
   * @return array<string, string|null>
   */
  public function toPayload(): array {
    return [
      'license.default' => $this->defaultLicense,
      'navigation.landingPage' => $this->defaultLandingPage,
    ];
  }

  public function getDefaultLicense(): ?License {
    return $this->defaultLicense ? License::tryFrom($this->defaultLicense) : null;
  }

  public function getDefaultLandingPage(): ?LandingPage {
    return $this->defaultLandingPage ? LandingPage::tryFrom($this->defaultLandingPage) : null;
  }

  public function shouldSyncLicenseToImages(): bool {
    return $this->syncLicenseToImages;
  }
}
