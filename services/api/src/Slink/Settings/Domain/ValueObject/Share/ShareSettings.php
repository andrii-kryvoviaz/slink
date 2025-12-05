<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Share;

use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\ValueObject\AbstractSettingsValueObject;

final readonly class ShareSettings extends AbstractSettingsValueObject {
  private function __construct(
    private bool $enableUrlShortening = true,
  ) {}

  #[\Override]
  public function toPayload(): array {
    return [
      'enableUrlShortening' => $this->enableUrlShortening,
    ];
  }

  #[\Override]
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['enableUrlShortening'] ?? true,
    );
  }

  #[\Override]
  public function getSettingsCategory(): SettingCategory {
    return SettingCategory::Share;
  }

  public function isEnableUrlShortening(): bool {
    return $this->enableUrlShortening;
  }
}
