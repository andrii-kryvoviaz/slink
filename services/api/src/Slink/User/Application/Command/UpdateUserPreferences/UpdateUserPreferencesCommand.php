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

final class UpdateUserPreferencesCommand implements CommandInterface {
  use EnvelopedMessage;

  #[SerializedName('license.default')]
  #[Assert\Choice(callback: [License::class, 'values'], message: 'Invalid license type.')]
  public ?string $defaultLicense;

  #[SerializedName('license.syncToImages')]
  public bool $syncLicenseToImages = false;

  #[SerializedName('navigation.landingPage')]
  #[Assert\Choice(callback: [LandingPage::class, 'values'], message: 'Invalid landing page.')]
  public ?string $defaultLandingPage;

  #[SerializedName('image.defaultVisibility')]
  #[Assert\Choice(callback: [DefaultVisibility::class, 'values'], message: 'Invalid default visibility.')]
  public ?string $defaultVisibility;

  #[SerializedName('display.language')]
  #[Assert\Choice(callback: [DisplayLanguage::class, 'values'], message: 'Invalid display language.')]
  public ?string $displayLanguage;

  #[SerializedName('display.theme')]
  #[Assert\Choice(callback: [DisplayTheme::class, 'values'], message: 'Invalid display theme.')]
  public ?string $displayTheme;

  #[SerializedName('image.externalUploadAutoPublish')]
  public ?bool $externalUploadAutoPublish;

  #[SerializedName('image.stripExifMetadataOverride')]
  #[Assert\Choice(callback: [ExifMetadataPreference::class, 'values'], message: 'Invalid EXIF metadata preference.')]
  public ?string $exifMetadataPreference;
}
