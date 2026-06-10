<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\UpdateUserPreferences;

use Slink\Image\Domain\Enum\License;
use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Slink\User\Domain\Enum\DefaultVisibility;
use Slink\User\Domain\Enum\DisplayLanguage;
use Slink\User\Domain\Enum\ExifMetadataPreference;
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
    #[SerializedName('image.defaultVisibility')]
    #[Assert\Choice(callback: [DefaultVisibility::class, 'values'], message: 'Invalid default visibility.')]
    private ?string $defaultVisibility = null,
    #[SerializedName('display.language')]
    #[Assert\Choice(callback: [DisplayLanguage::class, 'values'], message: 'Invalid display language.')]
    private ?string $displayLanguage = null,
    #[SerializedName('image.externalUploadAutoPublish')]
    private ?bool $externalUploadAutoPublish = null,
    #[SerializedName('image.stripExifMetadataOverride')]
    #[Assert\Choice(callback: [ExifMetadataPreference::class, 'values'], message: 'Invalid EXIF metadata preference.')]
    private ?string $exifMetadataPreference = null,
  ) {
  }

  public function getPreferences(): UserPreferences {
    return UserPreferences::create(
      defaultLicense: $this->getDefaultLicense(),
      defaultLandingPage: $this->getDefaultLandingPage(),
      defaultVisibility: $this->getDefaultVisibility(),
      displayLanguage: $this->getDisplayLanguage(),
      externalUploadAutoPublish: $this->externalUploadAutoPublish,
      exifMetadataPreference: $this->getExifMetadataPreference(),
    );
  }

  /**
   * @return array<string, string|bool|null>
   */
  public function toPayload(): array {
    return [
      'license.default' => $this->defaultLicense,
      'navigation.landingPage' => $this->defaultLandingPage,
      'image.defaultVisibility' => $this->defaultVisibility,
      'display.language' => $this->displayLanguage,
      'image.externalUploadAutoPublish' => $this->externalUploadAutoPublish,
      'image.stripExifMetadataOverride' => $this->exifMetadataPreference,
    ];
  }

  public function getDefaultLicense(): ?License {
    return $this->defaultLicense ? License::tryFrom($this->defaultLicense) : null;
  }

  public function getDefaultLandingPage(): ?LandingPage {
    return $this->defaultLandingPage ? LandingPage::tryFrom($this->defaultLandingPage) : null;
  }

  public function getDefaultVisibility(): ?DefaultVisibility {
    return $this->defaultVisibility ? DefaultVisibility::tryFrom($this->defaultVisibility) : null;
  }

  public function getDisplayLanguage(): ?DisplayLanguage {
    return $this->displayLanguage ? DisplayLanguage::tryFrom($this->displayLanguage) : null;
  }

  public function getExifMetadataPreference(): ?ExifMetadataPreference {
    return $this->exifMetadataPreference ? ExifMetadataPreference::tryFrom($this->exifMetadataPreference) : null;
  }

  public function shouldSyncLicenseToImages(): bool {
    return $this->syncLicenseToImages;
  }

  public function getExternalUploadAutoPublish(): ?bool {
    return $this->externalUploadAutoPublish;
  }
}
