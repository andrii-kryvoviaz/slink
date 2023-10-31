<?php

declare(strict_types=1);

namespace Slik\Image\Application\Command;

use Ramsey\Uuid\Uuid;
use Slik\Image\Domain\Image;
use Slik\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slik\Image\Domain\ValueObject\ImageAttributes;
use Slik\Shared\Application\Command\CommandHandlerInterface;
use Slik\Shared\Domain\Exception\DateTimeException;
use Slik\Shared\Domain\ValueObject\ID;
use Slik\Shared\Infrastructure\FileSystem\FileUploader;

final readonly class UploadImageHandler implements CommandHandlerInterface {
  
  public function __construct(private FileUploader $fileUploader, private ImageStoreRepositoryInterface $imageRepository) {
  }
  
  /**
   * @throws DateTimeException
   */
  public function __invoke(UploadImageCommand $command): void {
    $file = $command->getImageFile();
    
    $fileName = $this->fileUploader->upload($file, 'images');
    
    $image = Image::create(
      ID::fromString(Uuid::uuid4()->toString()),
      ImageAttributes::create(
        $fileName,
        $command->getDescription(),
        $command->isPublic(),
      )
    );
    
    $this->imageRepository->store($image);
  }
}