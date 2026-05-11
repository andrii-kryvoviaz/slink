<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Share;

use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\ValueObject\AbstractSettingsValueObject;

final readonly class ShareSettings extends AbstractSettingsValueObject {
  public const int DEFAULT_SHORT_URL_LENGTH = 8;
  public const int MIN_SHORT_URL_LENGTH = 4;
  public const int MAX_SHORT_URL_LENGTH = 32;

  private function __construct(
    private bool $enableUrlShortening = true,
    private int $shortUrlLength = self::DEFAULT_SHORT_URL_LENGTH,
  ) {}

  #[\Override]
  public function toPayload(): array {
    return [
      'enableUrlShortening' => $this->enableUrlShortening,
      'shortUrlLength' => $this->shortUrlLength,
    ];
  }

  #[\Override]
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['enableUrlShortening'] ?? true,
      self::clampShortUrlLength($payload['shortUrlLength'] ?? self::DEFAULT_SHORT_URL_LENGTH),
    );
  }

  #[\Override]
  public function getSettingsCategory(): SettingCategory {
    return SettingCategory::Share;
  }

  public function isEnableUrlShortening(): bool {
    return $this->enableUrlShortening;
  }

  public function getShortUrlLength(): int {
    return $this->shortUrlLength;
  }

  private static function clampShortUrlLength(mixed $value): int {
    $length = (int) $value;

    if ($length < self::MIN_SHORT_URL_LENGTH) {
      return self::MIN_SHORT_URL_LENGTH;
    }

    if ($length > self::MAX_SHORT_URL_LENGTH) {
      return self::MAX_SHORT_URL_LENGTH;
    }

    return $length;
  }
}
