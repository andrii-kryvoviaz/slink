<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem;

use Ramsey\Uuid\Nonstandard\Uuid;
use Slink\Shared\Infrastructure\FileSystem\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\File\File;

final readonly class FileUploader {
  const FILE_NAME_TEMPLATE = '%s.%s';
  
  /**
   * @param StorageInterface $storage
   */
  public function __construct(private StorageInterface $storage) {
  }
  
  /**
   * @param File $file
   * @param string|null $fileName
   * @param string|null $extension
   * @return string
   */
  public function upload(File $file, ?string $fileName = null, ?string $extension = null): string {
    $fullName = $this->generateFileName($file, $fileName, $extension);
    $this->storage->upload($file, $fullName);
    
    return $fullName;
  }
  
  /**
   * @param File $file
   * @param string|null $fileName
   * @param string|null $extension
   * @return string
   */
  private function generateFileName(File $file, ?string $fileName = null, ?string $extension = null): string {
    if(!$fileName) {
      return sprintf(self::FILE_NAME_TEMPLATE, Uuid::uuid4()->toString(), $file->guessExtension());
    }
    
    return sprintf(self::FILE_NAME_TEMPLATE, $fileName, $extension ?? $file->guessExtension());
  }
}