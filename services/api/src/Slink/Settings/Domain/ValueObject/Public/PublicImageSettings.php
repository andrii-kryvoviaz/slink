<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Public;

use Slink\Media\Domain\Enum\MediaFormat;
use Slink\Shared\Infrastructure\Attribute\Groups;

#[Groups(['public'])]
final readonly class PublicImageSettings {
  /**
   * @param list<string> $allowedMimeTypes
   */
  public function __construct(
    #[Groups(['public'])]
    public bool $enableLicensing = false,

    #[Groups(['public'])]
    public bool $allowOnlyPublicImages = false,

    #[Groups(['public'])]
    public bool $stripExifMetadata = true,

    #[Groups(['public'])]
    public string $chunkSize = '2M',

    #[Groups(['public'])]
    public array $allowedMimeTypes = [],
  ) {}

  /**
   * @param array<string, mixed> $settings
   */
  public static function fromArray(array $settings): self {
    return new self(
      $settings['enableLicensing'] ?? false,
      $settings['allowOnlyPublicImages'] ?? false,
      $settings['stripExifMetadata'] ?? true,
      $settings['chunkSize'] ?? '2M',
      MediaFormat::resolveMimeTypes((array) ($settings['allowedFormats'] ?? MediaFormat::allValues())),
    );
  }
}
