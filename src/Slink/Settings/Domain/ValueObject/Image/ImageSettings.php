<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Image;

use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\ValueObject\AbstractSettingsValueObject;

final readonly class ImageSettings extends AbstractSettingsValueObject {
  /**
   * @param string $maxSize
   * @param bool $stripExifMetadata
   */
  private function __construct(
    private string $maxSize,
    private bool $stripExifMetadata,
  ) {}
  
  /**
   * @inheritDoc
   */
  public function toPayload(): array {
    return [
      'maxSize' => $this->maxSize,
      'stripExifMetadata' => $this->stripExifMetadata
    ];
  }
  
  /**
   * @inheritDoc
   */
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['maxSize'],
      $payload['stripExifMetadata']
    );
  }
  
  /**
   * @inheritDoc
   */
  function getSettingsCategory(): SettingCategory {
    return SettingCategory::Image;
  }
  
  /**
   * @return string
   */
  public function getMaxSize(): string {
    return $this->maxSize;
  }
  
  /**
   * @return bool
   */
  public function isStripExifMetadata(): bool {
    return $this->stripExifMetadata;
  }
}