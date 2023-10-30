<?php

declare(strict_types=1);

namespace Slik\Image\Application\Command;

use Slik\Shared\Application\Command\CommandHandlerInterface;
use Slik\Shared\Infrastructure\FileSystem\FileUploader;

final readonly class UploadImageHandler implements CommandHandlerInterface {
  
  public function __construct(private FileUploader $fileUploader) {
  }
  public function __invoke(UploadImageCommand $command): void {
    $fileName = $this->fileUploader->upload($command->getImageFile(), 'images');
    
    // save to event store
  }
}