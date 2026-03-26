<?php

namespace Slink\Shared\Infrastructure\FileSystem\Storage;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\DirectoryStorageInterface;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\ObjectStorageInterface;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageCacheInterface;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;

abstract class AbstractStorage implements StorageInterface, StorageCacheInterface {
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
    ConfigurationProviderInterface $configurationProvider
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

  public function readImage(string $fileName): ?string {
    $path = $this->getPath($fileName);

    if (!$path) {
      return null;
    }

    return $this->read($path);
  }

  /**
   * @param ?string $fileName
   * @param bool $isCache
   * @return string|null
   */
  protected function getPath(?string $fileName = null, bool $isCache = false): ?string {
    if ($this instanceof ObjectStorageInterface) {
      return $fileName;
    }

    if ($this instanceof DirectoryStorageInterface) {
      return $this->handleDirectoryStorageProvider($fileName, $isCache);
    }

    return null;
  }

  private function handleDirectoryStorageProvider(?string $fileName = null, bool $isCache = false): ?string {
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

    if(!$fileName) {
      return $absolutePath;
    }

    return implode('/', [$absolutePath, $fileName]);
  }

  abstract public function clearCache(): int;

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
