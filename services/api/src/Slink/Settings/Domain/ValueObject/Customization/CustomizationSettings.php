<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Customization;

use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\ValueObject\AbstractSettingsValueObject;

final readonly class CustomizationSettings extends AbstractSettingsValueObject {
  public const string DEFAULT_SITE_NAME = 'Slink';
  public const string DEFAULT_SITE_DESCRIPTION = 'Fast and secure image sharing service';

  private function __construct(
    private string $siteName,
    private string $siteDescription,
    private string $faviconUrl,
  ) {}

  #[\Override]
  public function toPayload(): array {
    return [
      'siteName' => $this->siteName,
      'siteDescription' => $this->siteDescription,
      'faviconUrl' => $this->faviconUrl,
    ];
  }

  #[\Override]
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['siteName'] ?? self::DEFAULT_SITE_NAME,
      $payload['siteDescription'] ?? self::DEFAULT_SITE_DESCRIPTION,
      $payload['faviconUrl'] ?? '',
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

  public function getFaviconUrl(): string {
    return $this->faviconUrl;
  }
}
