<?php

declare(strict_types=1);

namespace Slink\Settings\Application\Command\UploadBrandingLogo;

use Slink\Image\Domain\Enum\ImageFormat;
use Slink\Image\Domain\Service\ImageFileTransformerInterface;
use Slink\Image\Domain\ValueObject\ImageConversionOptions;
use Slink\Settings\Domain\Enum\BrandingLogoFormat;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\FileSystem\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\File\File;

final readonly class UploadBrandingLogoHandler implements CommandHandlerInterface {
  public function __construct(
    private StorageInterface $storage,
    private ImageFileTransformerInterface $imageTransformer,
  ) {
  }

  /**
   * @return array{url: string}
   */
  public function __invoke(UploadBrandingLogoCommand $command): array {
    $file = $command->getFile();
    $format = BrandingLogoFormat::fromUploadedMimeType($file->getMimeType());

    if ($format->requiresConversion()) {
      $file = $this->imageTransformer->convertToFormat(
        new File($file->getPathname()),
        new ImageConversionOptions(format: ImageFormat::PNG),
      );
    }

    $digest = hash_file('sha256', $file->getPathname());

    if ($digest === false) {
      throw new \RuntimeException('Unable to hash branding logo file.');
    }

    foreach (BrandingLogoFormat::cases() as $existing) {
      $this->storage->delete($existing->fileName());
    }

    $this->storage->upload($file, $format->fileName());

    return ['url' => sprintf('/api/branding/logo?v=%s', substr($digest, 0, 8))];
  }
}
