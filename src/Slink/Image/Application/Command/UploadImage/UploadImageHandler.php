<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\UploadImage;

use Psr\Log\LoggerInterface;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\FileSystem\FileUploader;

final readonly class UploadImageHandler implements CommandHandlerInterface {
  
  public function __construct(
    private LoggerInterface $logger,
    private FileUploader $fileUploader,
    private ImageStoreRepositoryInterface $imageRepository,
    private ImageAnalyzerInterface $imageAnalyzer,
  ) {
  }
  
  /**
   * @throws DateTimeException
   */
  public function __invoke(UploadImageCommand $command, ?string $userId): void {
    $file = $command->getImageFile();
    $imageId = $command->getId();
    $userId = $userId
      ? ID::fromString($userId)
      : ID::generate();
    
    try {
      $this->imageAnalyzer->analyze($file);
    } catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }
    
    $metadata = $this->imageAnalyzer->toPayload();
    
    $fileName = $this->fileUploader->upload($file, $imageId->toString());
    
    $image = Image::create(
      $imageId,
      $userId,
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