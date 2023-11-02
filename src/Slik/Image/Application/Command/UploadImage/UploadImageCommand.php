<?php

declare(strict_types=1);

namespace Slik\Image\Application\Command\UploadImage;

use Slik\Shared\Application\Command\CommandInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UploadImageCommand implements CommandInterface {
  public function __construct(
    #[Assert\Image(maxSize: '5M', mimeTypes: ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/webp', 'image/svg+xml', 'image/svg'])]
    private File $image,
    
    private bool $isPublic = false,
    
    #[Assert\Length(max: 255)]
    private string $description = '',
  ) {
  }
  
  public function getImageFile(): File {
    return $this->image;
  }
  
  public function isPublic(): bool {
    return $this->isPublic;
  }
  
  public function getDescription(): string {
    return $this->description;
  }
}