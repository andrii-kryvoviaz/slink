<?php

declare(strict_types=1);

namespace Slik\Image\Application\Command\UploadImage;

use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Slik\Image\Domain\Image;
use Slik\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slik\Image\Domain\Service\ImageAnalyzer;
use Slik\Image\Domain\ValueObject\ImageAttributes;
use Slik\Image\Domain\ValueObject\ImageMetadata;
use Slik\Shared\Application\Command\CommandHandlerInterface;
use Slik\Shared\Domain\Exception\DateTimeException;
use Slik\Shared\Infrastructure\FileSystem\FileUploader;

final readonly class UploadImageHandler implements CommandHandlerInterface {
  
  public function __construct(private LoggerInterface $logger,private FileUploader $fileUploader, private ImageStoreRepositoryInterface $imageRepository) {
  }
  
  /**
   * @throws DateTimeException
   */
  public function __invoke(UploadImageCommand $command): void {
    $file = $command->getImageFile();
    $imageId = $command->getId();
    
    $imageAnalyzer = ImageAnalyzer::fromFile($file);
    
    try {
      $imageAnalyzer->analyze();
    } catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }
    
    $metadata = $imageAnalyzer->toPayload();
    
    $fileName = $this->fileUploader->upload($file, $imageId->toString());
    
    $image = Image::create(
      $imageId,
      ImageAttributes::create(
        $fileName,
        $command->getDescription(),
        $command->isPublic(),
      ),
      ImageMetadata::fromPayload($metadata),
    );
    
    $this->imageRepository->store($image);
  }
}