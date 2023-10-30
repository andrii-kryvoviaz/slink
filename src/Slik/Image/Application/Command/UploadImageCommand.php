<?php

declare(strict_types=1);

namespace Slik\Image\Application\Command;

use Slik\Shared\Application\Command\CommandInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UploadImageCommand implements CommandInterface {
  public function __construct(
    #[Assert\Image(maxSize: '5M', mimeTypes: ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/webp', 'image/svg+xml', 'image/svg'])]
    private File $image
  ) {
  }
  
  public function getImageFile(): File {
    return $this->image;
  }
}