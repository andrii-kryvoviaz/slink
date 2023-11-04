<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\FileSystem\Storage;

use Slik\Shared\Domain\ValueObject\ImageOptions;
use Slik\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\File\File;

final class LocalStorage extends AbstractStorage {
  
  /**
   * @param string $projectPublicDir
   */
  public function __construct(string $projectPublicDir) {
    $this->setServerRoot($projectPublicDir);
  }
  
  /**
   * @param File $file
   * @param ImageOptions|string $image
   * @return void
   */
  public function upload(File $file, ImageOptions|string $image): void {
    $path = $this->getAbsolutePath($image, onlyDir: true);
    
    if (!is_dir($path)) {
      mkdir($path, 0777, true);
    }
    
    $fileName = $this->getFileName($image);
    
    $file->move($path, $fileName);
  }
  
  /**
   * @throws NotFoundException
   */
  public function getImageContent(ImageOptions|string $image): string {
    $path = $this->getAbsolutePath($image);
    
    try {
      return file_get_contents($path);
    } catch (\Exception $e) {
      throw new NotFoundException();
    }
  }
  
  public function delete(ImageOptions|string $image): void {
    $path = $this->getAbsolutePath($image);
    
    unlink($path);
  }
  
  public function exists(ImageOptions|string $image): bool {
    $path = $this->getAbsolutePath($image);
    
    return file_exists($path);
  }
}