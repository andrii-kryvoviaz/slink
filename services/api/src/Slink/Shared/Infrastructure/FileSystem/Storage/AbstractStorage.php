<?php

namespace Slink\Shared\Infrastructure\FileSystem\Storage;

use Slink\Image\Domain\Service\ImageTransformerInterface;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\DirectoryStorageInterface;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\ObjectStorageInterface;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;

abstract class AbstractStorage implements StorageInterface {
  /**
   * @var string
   */
  protected string $imageDir = 'images' {
    set {
      $this->imageDir = $value;
    }
    get {
      return $this->imageDir;
    }
  }
  /**
   * @var string
   */
  protected string $cacheDir = 'cache' {
    set {
      $this->cacheDir = $value;
    }
    get {
      return $this->cacheDir;
    }
  }
  /**
   *
   */
  private const string APP_DIRECTORY = 'slink';
  
  /**
   * @var string|null
   */
  private ?string $serverRoot = null;
  
  public function __construct(
    private readonly ImageTransformerInterface $imageTransformer,
    ConfigurationProviderInterface             $configurationProvider
  ) {
    $this->init($configurationProvider);
  }
  
  /**
   * @param ConfigurationProviderInterface $configurationProvider
   * @return void
   */
  abstract function init(ConfigurationProviderInterface $configurationProvider): void;
  
  /**
   * @param string $serverRoot
   * @return void
   */
  protected function setServerRoot(string $serverRoot): void {
    $this->serverRoot = $serverRoot;
  }
  
  /**
   * @return string|null
   */
  private function getServerRoot(): ?string {
    return $this->serverRoot;
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
   * @param ImageOptions $image
   * @return string|null
   */
  public function getImage(ImageOptions $image): ?string {
    $imagePath = $this->getPath($image);
    
    if (!$imagePath) {
      return null;
    }
    
    if(!$image->isModified()) {
      return $this->read($imagePath);
    }
    
    $imageContent = $this->tryFromCache($image);
    
    if(!$imageContent) {
      return null;
    }
    
    return $imageContent;
  }
  
  /**
   * @param ?ImageOptions $image
   * @param bool $isCache
   * @return string|null
   */
  protected function getPath(?ImageOptions $image = null, bool $isCache = false): ?string {
    if ($this instanceof ObjectStorageInterface) {
      return $image?->getFileName($isCache);
    }
    
    if ($this instanceof DirectoryStorageInterface) {
      return $this->handleDirectoryStorageProvider($image, $isCache);
    }
    
    return null;
  }
  
  private function handleDirectoryStorageProvider(?ImageOptions $image = null, bool $isCache = false): ?string {
    if (!$this instanceof DirectoryStorageInterface) {
      return null;
    }
    
    $serverRoot = $this->getServerRoot();
    
    $path = $serverRoot
      ? [$serverRoot, self::APP_DIRECTORY]
      : [self::APP_DIRECTORY];
    
    $path[] = $isCache
      ? $this->getCacheDir()
      : $this->getImageDir();
    
    $absolutePath = implode('/', $path);
    
    if(!$this->exists($absolutePath)) {
      $this->mkdir($absolutePath);
    }
    
    if(!$image) {
      return $absolutePath;
    }
    
    $filename = $image->getFileName($isCache);
    
    return implode('/', [$absolutePath, $filename]);
  }
  
  /**
   * @param ImageOptions $image
   * @return string|null
   */
  private function tryFromCache(ImageOptions $image): ?string {
    $originalPath = $this->getPath($image);
    $cachePath = $this->getPath($image, isCache: true);
    
    if (!$originalPath || !$cachePath) {
      return null;
    }
    
    if($this->exists($cachePath)) {
      return $this->read($cachePath);
    }
    
    $originalContent = $this->read($originalPath);
    
    if(!$originalContent) {
      return null;
    }
    
    $content = $this->imageTransformer->transform($originalContent, $image);
    $this->write($cachePath, $content);
    
    return $content;
  }

  public function existsInCache(string $fileName): bool {
    $cachePath = $this->getCacheDirPath() . '/' . $fileName;
    return $this->exists($cachePath);
  }

  public function writeToCache(string $fileName, string $content): void {
    $cachePath = $this->getCacheDirPath() . '/' . $fileName;
    $this->write($cachePath, $content);
  }

  public function readFromCache(string $fileName): ?string {
    $cachePath = $this->getCacheDirPath() . '/' . $fileName;

    if (!$this->exists($cachePath)) {
      return null;
    }

    return $this->read($cachePath);
  }

  public function deleteFromCache(string $fileName): void {
    $cachePath = $this->getCacheDirPath() . '/' . $fileName;
    
    if ($this->exists($cachePath)) {
      if ($this instanceof DirectoryStorageInterface) {
        unlink($cachePath);
      } else {
        $this->delete($fileName);
      }
    }
  }

  private function getCacheDirPath(): string {
    if ($this instanceof ObjectStorageInterface) {
      return $this->getCacheDir();
    }

    $serverRoot = $this->getServerRoot();
    $path = $serverRoot
      ? [$serverRoot, self::APP_DIRECTORY, $this->getCacheDir()]
      : [self::APP_DIRECTORY, $this->getCacheDir()];
    
    $absolutePath = implode('/', $path);
    
    if ($this instanceof DirectoryStorageInterface && !$this->exists($absolutePath)) {
      $this->mkdir($absolutePath);
    }
    
    return $absolutePath;
  }
}