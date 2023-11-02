<?php

declare(strict_types=1);

namespace Slik\Image\Application\Command\UploadImage;

use Ramsey\Uuid\Uuid;
use Slik\Image\Domain\Image;
use Slik\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slik\Image\Domain\ValueObject\ImageAttributes;
use Slik\Image\Domain\ValueObject\ImageMetadata;
use Slik\Shared\Application\Command\CommandHandlerInterface;
use Slik\Shared\Domain\Exception\DateTimeException;
use Slik\Shared\Domain\ValueObject\ID;
use Slik\Shared\Infrastructure\FileSystem\FileUploader;
use Slik\Shared\Infrastructure\FileSystem\Storage\StorageInterface;

final readonly class UploadImageHandler implements CommandHandlerInterface {
  
  public function __construct(private FileUploader $fileUploader, private ImageStoreRepositoryInterface $imageRepository) {
  }
  
  /**
   * @throws DateTimeException
   */
  public function __invoke(UploadImageCommand $command): void {
    $file = $command->getImageFile();
    
    try {
      $parsedMetadata = exif_read_data($file->getRealPath());
    } catch (\Exception $e) {
      $parsedMetadata = null;
    }
    
    $imageId = ID::generate();
    $fileName = $this->fileUploader->upload($file, $imageId->toString());
    
    $image = Image::create(
      $imageId,
      ImageAttributes::create(
        $fileName,
        $command->getDescription(),
        $command->isPublic(),
      ),
      ImageMetadata::fromExifData($parsedMetadata)
    );
    
    $this->imageRepository->store($image);
  }
}