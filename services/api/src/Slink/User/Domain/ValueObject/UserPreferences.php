<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject;

use Slink\Image\Domain\Enum\License;
use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;
use Slink\User\Domain\Enum\DefaultVisibility;
use Slink\User\Domain\Enum\DisplayLanguage;
use Slink\User\Domain\Enum\ExifMetadataPreference;
use Slink\User\Domain\Enum\LandingPage;

final readonly class UserPreferences extends AbstractCompoundValueObject {
  /**
   * @param array<string, string|bool|null> $data
   */
  private function __construct(
    private array $data = [],
  ) {}

  public static function create(?License $defaultLicense = null, ?LandingPage $defaultLandingPage = null, ?DefaultVisibility $defaultVisibility = null, ?DisplayLanguage $displayLanguage = null, ?bool $externalUploadAutoPublish = null, ?ExifMetadataPreference $exifMetadataPreference = null): self {
    return new self([
      'license.default' => $defaultLicense?->value,
      'navigation.landingPage' => $defaultLandingPage?->value,
      'image.defaultVisibility' => $defaultVisibility?->value,
      'display.language' => $displayLanguage?->value,
      'image.externalUploadAutoPublish' => $externalUploadAutoPublish,
      'image.stripExifMetadataOverride' => $exifMetadataPreference?->value,
    ]);
  }

  public static function empty(): self {
    return new self();
  }

  public function getDefaultLicense(): ?License {
    return isset($this->data['license.default']) && is_string($this->data['license.default'])
      ? License::tryFrom($this->data['license.default'])
      : null;
  }

  public function getDefaultLandingPage(): ?LandingPage {
    return isset($this->data['navigation.landingPage']) && is_string($this->data['navigation.landingPage'])
      ? LandingPage::tryFrom($this->data['navigation.landingPage'])
      : null;
  }

  public function withDefaultLicense(?License $license): self {
    return new self([...$this->data, 'license.default' => $license?->value]);
  }

  public function withDefaultLandingPage(?LandingPage $landingPage): self {
    return new self([...$this->data, 'navigation.landingPage' => $landingPage?->value]);
  }

  public function getDefaultVisibility(): ?DefaultVisibility {
    return isset($this->data['image.defaultVisibility']) && is_string($this->data['image.defaultVisibility'])
      ? DefaultVisibility::tryFrom($this->data['image.defaultVisibility'])
      : null;
  }

  public function withDefaultVisibility(?DefaultVisibility $visibility): self {
    return new self([...$this->data, 'image.defaultVisibility' => $visibility?->value]);
  }

  public function resolveVisibility(bool $requested): bool {
    return $this->getDefaultVisibility()?->isPublic() ?? $requested;
  }

  public function getDisplayLanguage(): ?DisplayLanguage {
    return isset($this->data['display.language']) && is_string($this->data['display.language'])
      ? DisplayLanguage::tryFrom($this->data['display.language'])
      : null;
  }

  public function withDisplayLanguage(?DisplayLanguage $language): self {
    return new self([...$this->data, 'display.language' => $language?->value]);
  }

  public function getExternalUploadAutoPublish(): bool {
    return (bool) ($this->data['image.externalUploadAutoPublish'] ?? false);
  }

  public function withExternalUploadAutoPublish(?bool $value): self {
    return new self([...$this->data, 'image.externalUploadAutoPublish' => $value]);
  }

  public function getExifMetadataPreference(): ExifMetadataPreference {
    return isset($this->data['image.stripExifMetadataOverride']) && is_string($this->data['image.stripExifMetadataOverride'])
      ? ExifMetadataPreference::tryFrom($this->data['image.stripExifMetadataOverride']) ?? ExifMetadataPreference::Default
      : ExifMetadataPreference::Default;
  }

  public function withExifMetadataPreference(?ExifMetadataPreference $value): self {
    return new self([...$this->data, 'image.stripExifMetadataOverride' => $value?->value]);
  }

  /**
   * @param array<string, string|bool|null> $payload
   */
  public function applyChanges(array $payload): self {
    return new self([...$this->data, ...array_filter($payload, fn($v) => $v !== null)]);
  }

  /**
   * @return array<string, string|bool|null>
   */
  public function toPayload(): array {
    return $this->data;
  }

  /**
   * @param array<string, string|bool|null> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self($payload);
  }
}
