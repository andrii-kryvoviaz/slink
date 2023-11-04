<?php

namespace Slik\Shared\Infrastructure\FileSystem\Storage;

use Slik\Shared\Domain\ValueObject\ImageOptions;
use Slik\Shared\Infrastructure\FileSystem\Storage\Enum\StorageEntity;

abstract class AbstractStorage implements StorageInterface {
  protected string $imageDir = 'images';
  protected string $cacheDir = 'cache';
  private const APP_DIRECTORY = 'slink';
  
  private ?string $serverRoot = null;
  
  protected function setServerRoot(string $serverRoot): void {
    $this->serverRoot = $serverRoot;
  }
  
  public function setImageDir(string $imageDir): void {
    $this->imageDir = $imageDir;
  }
  
  public function setCacheDir(string $cacheDir): void {
    $this->cacheDir = $cacheDir;
  }
  
  private function getImageDir(): string {
    return $this->imageDir;
  }
  
  private function getCacheDir(): string {
    return $this->cacheDir;
  }
  
  protected function getRelativePath(ImageOptions|string $image, bool $onlyDir = false): string {
    $path = $this->getPath($image);
    $fileName = $this->getFileName($image);
    
    if ($onlyDir) {
      return implode('/', [self::APP_DIRECTORY, $path]);
    }
    
    return implode('/', [self::APP_DIRECTORY, $path, $fileName]);
  }
  
  protected function getAbsolutePath(ImageOptions|string $image, bool $onlyDir = false): string {
    $fileName = $this->getFileName($image);
    
    if(!$this->serverRoot) {
      return sprintf('/%s', $this->getRelativePath($fileName, $onlyDir));
    }
    
    return implode('/', [$this->serverRoot, $this->getRelativePath($fileName, $onlyDir)]);
  }
  
  public function getPath(ImageOptions|string $image): string {
    if ($image instanceof ImageOptions && $image->isModified()) {
      return $this->getCacheDir();
    }
    
    return $this->getImageDir();
  }
  
  protected function getFileName(ImageOptions|string $image): string {
    if ($image instanceof ImageOptions) {
      return $image->getFileName();
    }
    
    return $image;
  }
}