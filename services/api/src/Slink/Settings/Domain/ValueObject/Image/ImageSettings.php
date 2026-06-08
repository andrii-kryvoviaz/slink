<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Image;

use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\Exception\InvalidChunkSizeException;
use Slink\Settings\Domain\Exception\InvalidImageMaxSizeException;
use Slink\Settings\Domain\ValueObject\AbstractSettingsValueObject;

final readonly class ImageSettings extends AbstractSettingsValueObject {
  private const int MIN_CHUNK_SIZE_BYTES = 1 * 1024 * 1024;
  private const int MAX_CHUNK_SIZE_BYTES = 25 * 1024 * 1024;

  /**
   * @param string $maxSize
   * @param string $chunkSize
   * @param bool $stripExifMetadata
   * @param int $compressionQuality
   * @param bool $allowOnlyPublicImages
   * @param bool $enableDeduplication
   * @param bool $enableLicensing
   * @param bool $forceFormatConversion
   * @param string|null $targetFormat
   * @param bool $convertAnimatedImages
   */
  private function __construct(
    private string $maxSize,
    private string $chunkSize = '2M',
    private bool $stripExifMetadata = true,
    private int $compressionQuality = 80,
    private bool $allowOnlyPublicImages = false,
    private bool $enableDeduplication = true,
    private bool $enableLicensing = false,
    private bool $forceFormatConversion = false,
    private ?string $targetFormat = null,
    private bool $convertAnimatedImages = false,
  ) {
    if (!preg_match('/^(\d+)([kM])$/', $maxSize)) {
      throw new InvalidImageMaxSizeException();
    }
    
    if ((int) $maxSize < 0) {
      throw new InvalidImageMaxSizeException('Max size cannot be less than 0');
    }
    
    if ((int) $maxSize > 1000) {
      throw new InvalidImageMaxSizeException('Max size cannot be greater than 1000');
    }

    if (!preg_match('/^(\d+)([kM])$/', $chunkSize)) {
      throw new InvalidChunkSizeException();
    }

    $chunkSizeBytes = convertSizeToBytes($chunkSize);

    if ($chunkSizeBytes < self::MIN_CHUNK_SIZE_BYTES) {
      throw new InvalidChunkSizeException('Chunk size cannot be smaller than 1M');
    }

    if ($chunkSizeBytes > self::MAX_CHUNK_SIZE_BYTES) {
      throw new InvalidChunkSizeException('Chunk size cannot be greater than 25M');
    }
  }
  
  /**
   * @inheritDoc
   */
  public function toPayload(): array {
    return [
      'maxSize' => $this->maxSize,
      'chunkSize' => $this->chunkSize,
      'stripExifMetadata' => $this->stripExifMetadata,
      'compressionQuality' => $this->compressionQuality,
      'allowOnlyPublicImages' => $this->allowOnlyPublicImages,
      'enableDeduplication' => $this->enableDeduplication,
      'enableLicensing' => $this->enableLicensing,
      'forceFormatConversion' => $this->forceFormatConversion,
      'targetFormat' => $this->targetFormat,
      'convertAnimatedImages' => $this->convertAnimatedImages,
    ];
  }
  
  /**
   * @inheritDoc
   */
  public static function fromPayload(array $payload): static {
    $chunkSize = $payload['chunkSize'] ?? '2M';

    if (!is_string($chunkSize) || !preg_match('/^(\d+)([kM])$/', $chunkSize)) {
      $chunkSize = convertBytesToSize((int) $chunkSize);
    }

    return new self(
      $payload['maxSize'],
      $chunkSize,
      $payload['stripExifMetadata'] ?? true,
      $payload['compressionQuality'] ?? 80,
      $payload['allowOnlyPublicImages'] ?? false,
      $payload['enableDeduplication'] ?? true,
      $payload['enableLicensing'] ?? false,
      $payload['forceFormatConversion'] ?? false,
      $payload['targetFormat'] ?? null,
      $payload['convertAnimatedImages'] ?? false,
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
   * @return string
   */
  public function getChunkSize(): string {
    return $this->chunkSize;
  }
  
  /**
   * @return bool
   */
  public function isStripExifMetadata(): bool {
    return $this->stripExifMetadata;
  }
  
  /**
   * @return int
   */
  public function getCompressionQuality(): int {
    return $this->compressionQuality;
  }
  
  /**
   * @return bool
   */
  public function isAllowOnlyPublicImages(): bool {
    return $this->allowOnlyPublicImages;
  }

  /**
   * @return bool
   */
  public function isEnableDeduplication(): bool {
    return $this->enableDeduplication;
  }

  /**
   * @return bool
   */
  public function isEnableLicensing(): bool {
    return $this->enableLicensing;
  }

  /**
   * @return bool
   */
  public function isForceFormatConversion(): bool {
    return $this->forceFormatConversion;
  }

  /**
   * @return string|null
   */
  public function getTargetFormat(): ?string {
    return $this->targetFormat;
  }

  /**
   * @return bool
   */
  public function isConvertAnimatedImages(): bool {
    return $this->convertAnimatedImages;
  }
}