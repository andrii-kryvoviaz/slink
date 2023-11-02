<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\FileSystem\Storage;

use Symfony\Component\HttpFoundation\File\File;

interface StorageInterface {
  public const PUBLIC_PATH = 'images';
  public function upload(File $file, string $fileName): void;
  
  public function getPath(string $fileName): string;
  
  public function delete(string $fileName): void;
  
  public function exists(string $fileName): bool;
  
  public function getImageContent(string $fileName): string;
}