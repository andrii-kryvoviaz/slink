<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Public;

use Slink\Media\Domain\Enum\MediaFormat;
use Slink\Settings\Domain\ValueObject\Image\ImageSettings;
use Slink\Shared\Infrastructure\Attribute\Groups;

#[Groups(['public'])]
final readonly class PublicImageSettings {
  /**
   * @param list<string> $allowedMimeTypes
   * @param list<string> $allowedFormatLabels
   */
  public function __construct(
    #[Groups(['public'])]
    public bool $enableLicensing = false,

    #[Groups(['public'])]
    public bool $allowOnlyPublicImages = false,

    #[Groups(['public'])]
    public bool $stripExifMetadata = true,

    #[Groups(['public'])]
    public string $maxSize = '15M',

    #[Groups(['public'])]
    public string $chunkSize = '2M',

    #[Groups(['public'])]
    public array $allowedMimeTypes = [],

    #[Groups(['public'])]
    public array $allowedFormatLabels = [],
  ) {}

  /**
   * @param array<string, mixed> $settings
   */
  public static function fromArray(array $settings): self {
    $imageSettings = ImageSettings::fromPayload($settings);

    return new self(
      $settings['enableLicensing'] ?? false,
      $settings['allowOnlyPublicImages'] ?? false,
      $settings['stripExifMetadata'] ?? true,
      $settings['maxSize'] ?? '15M',
      $settings['chunkSize'] ?? '2M',
      $imageSettings->getAllowedMimeTypes(),
      array_map(
        static fn (string $format): string => MediaFormat::from($format)->label(),
        $imageSettings->getAllowedFormats(),
      ),
    );
  }
}
