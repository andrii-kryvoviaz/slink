<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\FileSystem\Storage;

use Slik\Shared\Infrastructure\FileSystem\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\File\File;

final readonly class LocalStorage implements StorageInterface {
  
  /**
   * @param string $projectPublicDir
   * @param string $directory
   */
  public function __construct(private string $projectPublicDir, private string $directory = self::PUBLIC_PATH) {
  }
  
  /**
   * @param File $file
   * @param string $fileName
   * @return void
   */
  public function upload(File $file, string $fileName): void {
    $path = $this->directory ? $this->projectPublicDir . '/' . $this->directory : $this->projectPublicDir;
    
    if (!is_dir($path)) {
      mkdir($path, 0777, true);
    }
    
    $file->move($path, $fileName);
  }
  
  public function getPath(string $fileName): string {
    $path = $this->directory ? $this->projectPublicDir . '/' . $this->directory : $this->projectPublicDir;
    
    return $path . '/' . $fileName;
  }
  
  public function delete(string $fileName): void {
    unlink($this->getPath($fileName));
  }
  
  public function exists(string $fileName): bool {
    return file_exists($this->getPath($fileName));
  }
  
  public function getImageContent(string $fileName): string {
    return file_get_contents($this->getPath($fileName));
  }
}