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
      $this->mkdir($path);
    }
    
    $fileName = $this->getFileName($image);
    
    $file->move($path, $fileName);
  }
  
  public function write(string $path, string $content): void {
    file_put_contents($path, $content);
  }
  
  /**
   * @throws NotFoundException
   */
  public function read(string $path): ?string {
    try {
      $content = file_get_contents($path);
      return $content === false ? null : $content;
    } catch (\Exception $e) {
      throw new NotFoundException();
    }
  }
  
  public function delete(ImageOptions|string $image): void {
    $path = $this->getAbsolutePath($image);
    
    unlink($path);
  }
  
  public function exists(string $path): bool {
    return file_exists($path);
  }
  
  public function mkdir(string $path): void {
    mkdir($path, 0777, true);
  }
}