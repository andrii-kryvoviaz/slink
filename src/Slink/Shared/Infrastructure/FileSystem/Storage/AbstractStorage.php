<?php

namespace Slink\Shared\Infrastructure\FileSystem\Storage;

use Gumlet\ImageResizeException;
use Icewind\SMB\Exception\InvalidTypeException;
use Icewind\SMB\Exception\NotFoundException;
use Slink\Image\Domain\Service\ImageTransformerInterface;
use Slink\Image\Infrastructure\Service\ImageTransformer;
use Slink\Settings\Domain\Service\ConfigurationProvider;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Symfony\Contracts\Service\Attribute\Required;

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
  private const string APP_DIRECTORY = 'slink';
  
  /**
   * @var string|null
   */
  private ?string $serverRoot = null;
  
  /**
   * @param ConfigurationProvider $configurationProvider
   * @return static
   */
  abstract static function create(ConfigurationProvider $configurationProvider): self;
  
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
   * @throws ImageResizeException
   * @throws NotFoundException
   */
  protected function getActualPath(ImageOptions|string $image): string {
    if (!$this->isModified($image)) {
      return $this->getAbsolutePath($image);
    }
    
    // Handle transformations such as resize, crop, etc.
    // Cache the image in the cache directory
    $cachePath = $this->getAbsolutePath($image, onlyDir: true);
    
    if (!$this->exists($cachePath)) {
      $this->mkdir($cachePath);
    }
    
    $originalImagePath = $this->getOriginalPath($image);
    $cacheImagePath = $this->getAbsolutePath($image);
    
    if (!$this->exists($cacheImagePath)) {
      // apply transformations
      $imageTransformer = ImageTransformer::create();
      
      if(is_string($image)) {
        return $originalImagePath;
      }
      
      $originalContent = $this->read($originalImagePath);
      
      if(!$originalContent) {
        throw new NotFoundException(sprintf('Image not found: %s', $originalImagePath));
      }
      
      $content = $imageTransformer->transform($originalContent, $image->toPayload());
      
      $this->write($cacheImagePath, $content);
    }
    
    return $cacheImagePath;
  }
  
  /**
   * @param ImageOptions|string|null $image
   * @return string|null
   * @throws ImageResizeException|NotFoundException
   */
  public function getImage(ImageOptions|string|null $image): ?string {
    if(!$image) {
      return null;
    }
    
    return $this->read($this->getActualPath($image));
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
    if(is_string($image)) {
      return $image;
    }
    
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