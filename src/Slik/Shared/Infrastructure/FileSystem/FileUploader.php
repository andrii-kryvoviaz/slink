<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\FileSystem;

use Ramsey\Uuid\Nonstandard\Uuid;
use Slik\Shared\Infrastructure\FileSystem\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\File\File;

final readonly class FileUploader {
  
  /**
   * @param StorageInterface $storage
   */
  public function __construct(private StorageInterface $storage) {
  }
  
  /**
   * @param File $file
   * @param string|null $path
   * @param string|null $fileName
   * @return string
   */
  public function upload(File $file, ?string $path = null, ?string $fileName = null): string {
    $fileName = $fileName ?? $this->generateFileName($file);
    $this->storage->upload($file, $fileName, $path);
    
    return $fileName;
  }
  
  /**
   * @param File $file
   * @return string
   */
  private function generateFileName(File $file): string {
    return sprintf('%s.%s', Uuid::uuid4()->toString(), $file->guessExtension());
  }
}