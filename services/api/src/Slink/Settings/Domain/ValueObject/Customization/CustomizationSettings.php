<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Customization;

use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\Exception\InvalidLogoUrlException;
use Slink\Settings\Domain\Exception\InvalidSiteDescriptionException;
use Slink\Settings\Domain\Exception\InvalidSiteNameException;
use Slink\Settings\Domain\ValueObject\AbstractSettingsValueObject;

final readonly class CustomizationSettings extends AbstractSettingsValueObject {
  public const string DEFAULT_SITE_NAME = 'Slink';
  public const string DEFAULT_SITE_DESCRIPTION = 'Fast and secure image sharing service';

  private const int MAX_SITE_NAME_LENGTH = 64;
  private const int MAX_SITE_DESCRIPTION_LENGTH = 255;

  private function __construct(
    private string $siteName,
    private string $siteDescription,
    private string $logoUrl,
  ) {
    if (mb_strlen($siteName) > self::MAX_SITE_NAME_LENGTH) {
      throw new InvalidSiteNameException('Site name cannot be longer than 64 characters');
    }

    if (mb_strlen($siteDescription) > self::MAX_SITE_DESCRIPTION_LENGTH) {
      throw new InvalidSiteDescriptionException('Site description cannot be longer than 255 characters');
    }

    if ($logoUrl !== '' && !filter_var($logoUrl, FILTER_VALIDATE_URL) && !str_starts_with($logoUrl, '/')) {
      throw new InvalidLogoUrlException();
    }
  }

  #[\Override]
  public function toPayload(): array {
    return [
      'siteName' => $this->siteName,
      'siteDescription' => $this->siteDescription,
      'logoUrl' => $this->logoUrl,
    ];
  }

  #[\Override]
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['siteName'] ?? self::DEFAULT_SITE_NAME,
      $payload['siteDescription'] ?? self::DEFAULT_SITE_DESCRIPTION,
      $payload['logoUrl'] ?? '',
    );
  }

  #[\Override]
  public function getSettingsCategory(): SettingCategory {
    return SettingCategory::Customization;
  }

  public function getSiteName(): string {
    return $this->siteName;
  }

  public function getSiteDescription(): string {
    return $this->siteDescription;
  }

  public function getLogoUrl(): string {
    return $this->logoUrl;
  }
}
