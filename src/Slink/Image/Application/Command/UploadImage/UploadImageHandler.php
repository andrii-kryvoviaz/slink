<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\UploadImage;

use Slink\Image\Domain\Image;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageTransformerInterface;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\FileSystem\Storage\StorageInterface;

final readonly class UploadImageHandler implements CommandHandlerInterface {
  
  /**
   * @param ConfigurationProviderInterface<SettingsService> $configurationProvider
   * @param ImageStoreRepositoryInterface  $imageRepository
   * @param ImageAnalyzerInterface         $imageAnalyzer
   * @param ImageTransformerInterface      $imageTransformer
   * @param StorageInterface               $storage
   */
  public function __construct(
    private ConfigurationProviderInterface $configurationProvider,
    private ImageStoreRepositoryInterface  $imageRepository,
    private ImageAnalyzerInterface         $imageAnalyzer,
    private ImageTransformerInterface      $imageTransformer,
    private StorageInterface               $storage
  ) {
  }
  
  /**
   * @throws DateTimeException
   */
  public function __invoke(UploadImageCommand $command, ?string $userId = null): void {
    $file = $command->getImageFile();
    $imageId = $command->getId();
    
    $userId = $userId
      ? ID::fromString($userId)
      : ID::generate();
    
    if($this->imageAnalyzer->isConversionRequired($file->getMimeType())) {
      $file = $this->imageTransformer->convertToJpeg($file);
    }
    
    [$mimeType, $pathname, $extension] = [$file->getMimeType(), $file->getPathname(), $file->guessExtension()];
    
    if(
      $this->imageAnalyzer->supportsExifProfile($mimeType)
      && $this->configurationProvider->get('image.stripExifMetadata')
    ) {
      $this->imageTransformer->stripExifMetadata($pathname);
    }
    
    $fileName = sprintf('%s.%s', $imageId, $extension);
    
    $metadata = ImageMetadata::fromPayload(
      $this->imageAnalyzer->analyze($file),
    );
    
    $attributes = ImageAttributes::create(
      $fileName,
      $command->getDescription(),
      $command->isPublic(),
    );
    
    $this->storage->upload($file, $fileName);
    
    $image = Image::create(
      $imageId,
      $userId,
      $attributes,
      $metadata,
    );
    
    $this->imageRepository->store($image);
  }
}