<?php

namespace Slik\Shared\Infrastructure\FileSystem\Storage;

use Slik\Shared\Domain\ValueObject\ImageOptions;
use Slik\Shared\Infrastructure\FileSystem\Storage\Enum\StorageEntity;

abstract class AbstractStorage implements StorageInterface {
  /**
   * @var string
   */
  protected string $imageDir = 'images';
  /**
   * @var string
   */
  protected string $cacheDir = 'cache';
  /**
   *
   */
  private const APP_DIRECTORY = 'slink';
  
  /**
   * @var string|null
   */
  private ?string $serverRoot = null;
  
  /**
   * @param string $serverRoot
   * @return void
   */
  protected function setServerRoot(string $serverRoot): void {
    $this->serverRoot = $serverRoot;
  }
  
  /**
   * @param string $imageDir
   * @return void
   */
  public function setImageDir(string $imageDir): void {
    $this->imageDir = $imageDir;
  }
  
  /**
   * @param string $cacheDir
   * @return void
   */
  public function setCacheDir(string $cacheDir): void {
    $this->cacheDir = $cacheDir;
  }
  
  /**
   * @return string
   */
  private function getImageDir(): string {
    return $this->imageDir;
  }
  
  /**
   * @return string
   */
  private function getCacheDir(): string {
    return $this->cacheDir;
  }
  
  /**
   * @param ImageOptions|string $image
   * @param bool $onlyDir
   * @return string
   */
  protected function getRelativePath(ImageOptions|string $image, bool $onlyDir = false): string {
    $path = $this->getPath($image);
    $fileName = $this->getFileName($image);
    
    if ($onlyDir) {
      return implode('/', [self::APP_DIRECTORY, $path]);
    }
    
    return implode('/', [self::APP_DIRECTORY, $path, $fileName]);
  }
  
  /**
   * @param ImageOptions|string $image
   * @param bool $onlyDir
   * @return string
   */
  protected function getAbsolutePath(ImageOptions|string $image, bool $onlyDir = false): string {
    if(!$this->serverRoot) {
      return sprintf('/%s', $this->getRelativePath($image, $onlyDir));
    }
    
    return implode('/', [$this->serverRoot, $this->getRelativePath($image, $onlyDir)]);
  }
  
  /**
   * @param ImageOptions|string $image
   * @return string
   */
  protected function getOriginalPath(ImageOptions|string $image): string {
    return $this->getAbsolutePath((string) $image);
  }
  
  /**
   * @param ImageOptions|string $image
   * @return string
   */
  protected function getPath(ImageOptions|string $image): string {
    if ($this->isModified($image)) {
      return $this->getCacheDir();
    }
    
    return $this->getImageDir();
  }
  
  /**
   * @param ImageOptions|string $image
   * @return string
   */
  protected function getFileName(ImageOptions|string $image): string {
    if ($this->isModified($image)) {
      return $image->getCacheFileName();
    }
    
    return $image->getFileName();
  }
  
  /**
   * @param ImageOptions|string $image
   * @return bool
   */
  protected function isModified(ImageOptions|string $image): bool {
    if ($image instanceof ImageOptions) {
      return $image->isModified();
    }
    
    return false;
  }
}