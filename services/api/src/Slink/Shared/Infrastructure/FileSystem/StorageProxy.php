<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\Enum\StorageProvider;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\HttpFoundation\File\File;

#[AsAlias(StorageInterface::class)]
final class StorageProxy implements StorageInterface {
  protected StorageInterface $storageProvider {
    get => $this->storageProviderLocator->get(
      StorageProvider::from($this->configurationProvider->get('storage.provider'))
    );
  }
  
  public function __construct(
    private readonly StorageProviderLocator $storageProviderLocator,
    private readonly ConfigurationProviderInterface $configurationProvider
  ) {
  }
  
  /**
   * @param File $file
   * @param string $fileName
   * @return void
   */
  public function upload(File $file, string $fileName): void {
    $this->storageProvider->upload($file, $fileName);
  }
  
  /**
   * @param ImageOptions $image
   * @return string|null
   */
  public function getImage(ImageOptions $image): ?string {
    return $this->storageProvider->getImage($image);
  }
  
  /**
   * @param string $fileName
   * @return void
   */
  public function delete(string $fileName): void {
    $this->storageProvider->delete($fileName);
  }
  
  /**
   * @param string $path
   * @return bool
   */
  public function exists(string $path): bool {
    return $this->storageProvider->exists($path);
  }
  
  /**
   * @param string $path
   * @param string $content
   * @return void
   */
  public function write(string $path, string $content): void {
    $this->storageProvider->write($path, $content);
  }
  
  /**
   * @param string $path
   * @return string|null
   */
  public function read(string $path): ?string {
    return $this->storageProvider->read($path);
  }
  
  /**
   * @return string
   */
  public static function getAlias(): string {
    return 'storage.proxy';
  }
}