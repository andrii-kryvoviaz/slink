<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\FileSystem\Storage;

use Slik\Shared\Domain\ValueObject\ImageOptions;
use Symfony\Component\HttpFoundation\File\File;

interface StorageInterface {
  public function upload(File $file, ImageOptions|string $image): void;
  
  public function getImageContent(ImageOptions|string $image): string;
  
  public function delete(ImageOptions|string $image): void;
  
  public function exists(ImageOptions|string $image): bool;
}