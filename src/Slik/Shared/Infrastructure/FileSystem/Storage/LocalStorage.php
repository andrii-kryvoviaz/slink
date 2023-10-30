<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\FileSystem\Storage;

use Slik\Shared\Infrastructure\FileSystem\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\File\File;

final readonly class LocalStorage implements StorageInterface {
  
  /**
   * @param string $projectPublicDir
   */
  public function __construct(private string $projectPublicDir) {
  }
  
  /**
   * @param File $file
   * @param string $fileName
   * @param string|null $path
   * @return void
   */
  public function upload(File $file, string $fileName, ?string $path = null): void {
    $path = $path ? $this->projectPublicDir . '/' . $path : $this->projectPublicDir;
    
    if (!is_dir($path)) {
      mkdir($path, 0777, true);
    }
    
    $file->move($path, $fileName);
  }
}