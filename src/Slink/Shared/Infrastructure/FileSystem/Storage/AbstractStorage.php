<?php

namespace Slink\Shared\Infrastructure\FileSystem\Storage;

use Icewind\SMB\Exception\NotFoundException;
use Slink\Image\Domain\Service\ImageTransformerInterface;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\ValueObject\ImageOptions;

abstract class AbstractStorage implements StorageInterface {
  /**
   * @var string
   */
  protected string $imageDir = 'images' {
    set {
      $this->imageDir = $value;
    }
  }
  /**
   * @var string
   */
  protected string $cacheDir = 'cache' {
    set {
      $this->cacheDir = $value;
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
   * @throws NotFoundException
   */
  public function getImage(ImageOptions $image): ?string {
    if(!$image->isModified()) {
      return $this->read($this->getPath($image));
    }
    
    $imageContent = $this->tryFromCache($image);
    
    if(!$imageContent) {
      throw new NotFoundException(sprintf('Image not found: %s', $this->getPath($image)));
    }
    
    return $imageContent;
  }
  
  /**
   * @param ?ImageOptions $image
   * @param bool $isCache
   * @return string
   */
  protected function getPath(?ImageOptions $image = null, bool $isCache = false): string {
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
    
    $filename = $isCache
      ? $image->getCacheFileName()
      : $image->getFileName();
    
    return implode('/', [$absolutePath, $filename]);
  }
  
  /**
   * @param ImageOptions $image
   * @return string|null
   */
  private function tryFromCache(ImageOptions $image): ?string {
    $originalPath = $this->getPath($image);
    $cachePath = $this->getPath($image, isCache: true);
    
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
}