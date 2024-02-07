<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\FileSystem\Storage;

use Slik\Shared\Domain\ValueObject\ImageOptions;
use Symfony\Component\HttpFoundation\File\File;

interface StorageInterface {
  public function upload(File $file, ImageOptions|string $image): void;
  
  public function getImage(ImageOptions|string|null $image): ?string;
  
  public function delete(ImageOptions|string $image): void;
  
  public function exists(string $path): bool;
  
  public function mkdir(string $path): void;
  
  public function write(string $path, string $content): void;
  
  public function read(string $path): ?string;
}