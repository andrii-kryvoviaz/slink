<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\UploadImage;

use Slink\Image\Domain\Image;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageTransformerInterface;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\FileSystem\Storage\StorageInterface;

final readonly class UploadImageHandler implements CommandHandlerInterface {
  
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
    $fileName = sprintf('%s.%s', $imageId, $file->guessExtension());
    
    $userId = $userId
      ? ID::fromString($userId)
      : ID::generate();
    
    $metadata = ImageMetadata::fromPayload(
      $this->imageAnalyzer->analyze($file),
    );
    
    $attributes = ImageAttributes::create(
      $fileName,
      $command->getDescription(),
      $command->isPublic(),
    );
    
    if(
      $this->imageAnalyzer->supportsExifProfile($file->getMimeType())
      && $this->configurationProvider->get('image.stripExifMetadata')
    ) {
      $this->imageTransformer->stripExifMetadata($file->getPathname());
    }
    
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