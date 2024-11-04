<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem\Storage;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Symfony\Component\HttpFoundation\File\File;

interface StorageInterface {
  /**
   * @param ConfigurationProviderInterface $configurationProvider
   * @return static
   */
  static function create(ConfigurationProviderInterface $configurationProvider): self;
  
  /**
   * @param File $file
   * @param string $fileName
   * @return void
   */
  public function upload(File $file, string $fileName): void;
  
  /**
   * @param ImageOptions $image
   * @return string|null
   */
  public function getImage(ImageOptions $image): ?string;
  
  /**
   * @param string $fileName
   * @return void
   */
  public function delete(string $fileName): void;
  
  /**
   * @param string $path
   * @return bool
   */
  public function exists(string $path): bool;
  
  /**
   * @param string $path
   * @return void
   */
  public function mkdir(string $path): void;
  
  /**
   * @param string $path
   * @param string $content
   * @return void
   */
  public function write(string $path, string $content): void;
  
  /**
   * @param string $path
   * @return string|null
   */
  public function read(string $path): ?string;
}