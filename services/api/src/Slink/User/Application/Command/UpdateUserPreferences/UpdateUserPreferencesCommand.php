<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\UpdateUserPreferences;

use Slink\Image\Domain\Enum\License;
use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Slink\User\Domain\Enum\DefaultVisibility;
use Slink\User\Domain\Enum\DisplayLanguage;
use Slink\User\Domain\Enum\DisplayTheme;
use Slink\User\Domain\Enum\ExifMetadataPreference;
use Slink\User\Domain\Enum\LandingPage;
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
    #[SerializedName('display.theme')]
    #[Assert\Choice(callback: [DisplayTheme::class, 'values'], message: 'Invalid display theme.')]
    private ?string $displayTheme = null,
    #[SerializedName('image.externalUploadAutoPublish')]
    private ?bool $externalUploadAutoPublish = null,
    #[SerializedName('image.stripExifMetadataOverride')]
    #[Assert\Choice(callback: [ExifMetadataPreference::class, 'values'], message: 'Invalid EXIF metadata preference.')]
    private ?string $exifMetadataPreference = null,
  ) {
  }

  public function getDefaultLicense(): ?string {
    return $this->defaultLicense;
  }

  public function getDefaultLandingPage(): ?string {
    return $this->defaultLandingPage;
  }

  public function getDefaultVisibility(): ?string {
    return $this->defaultVisibility;
  }

  public function getDisplayLanguage(): ?string {
    return $this->displayLanguage;
  }

  public function getDisplayTheme(): ?string {
    return $this->displayTheme;
  }

  public function getExternalUploadAutoPublish(): ?bool {
    return $this->externalUploadAutoPublish;
  }

  public function getExifMetadataPreference(): ?string {
    return $this->exifMetadataPreference;
  }

  public function shouldSyncLicenseToImages(): bool {
    return $this->syncLicenseToImages;
  }
}
