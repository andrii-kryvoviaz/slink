<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Access;

use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\ValueObject\AbstractSettingsValueObject;

final readonly class AccessSettings extends AbstractSettingsValueObject {
  private function __construct(
    private bool $allowGuestUploads = false,
    private bool $allowUnauthenticatedAccess = false,
    private bool $requireSsl = false,
  ) {}
  
  #[\Override]
  public function toPayload(): array {
    return [
      'allowGuestUploads' => $this->allowGuestUploads,
      'allowUnauthenticatedAccess' => $this->allowUnauthenticatedAccess,
      'requireSsl' => $this->requireSsl,
    ];
  }
  
  #[\Override]
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['allowGuestUploads'] ?? false,
      $payload['allowUnauthenticatedAccess'] ?? false,
      $payload['requireSsl'] ?? false,
    );
  }
  
  #[\Override]
  public function getSettingsCategory(): SettingCategory {
    return SettingCategory::Access;
  }
  
  public function isAllowGuestUploads(): bool {
    return $this->allowGuestUploads;
  }
  
  public function isAllowUnauthenticatedAccess(): bool {
    return $this->allowUnauthenticatedAccess;
  }
  
  public function isRequireSsl(): bool {
    return $this->requireSsl;
  }
}
