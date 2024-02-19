<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem\Storage;

use Slink\Settings\Domain\Service\ConfigurationProvider;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Symfony\Component\HttpFoundation\File\File;

interface StorageInterface {
  /**
   * @param ConfigurationProvider $configurationProvider
   * @return static
   */
  static function create(ConfigurationProvider $configurationProvider): self;
  
  /**
   * @param File $file
   * @param ImageOptions|string $image
   * @return void
   */
  public function upload(File $file, ImageOptions|string $image): void;
  
  /**
   * @param ImageOptions|string|null $image
   * @return string|null
   */
  public function getImage(ImageOptions|string|null $image): ?string;
  
  /**
   * @param ImageOptions|string $image
   * @return void
   */
  public function delete(ImageOptions|string $image): void;
  
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