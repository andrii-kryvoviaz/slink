<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem\Storage\Contract;

use Slink\Shared\Domain\ValueObject\ImageOptions;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\File\File;

#[AutoconfigureTag]
interface StorageInterface {
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
   * @param string $content
   * @return void
   */
  public function write(string $path, string $content): void;
  
  /**
   * @param string $path
   * @return string|null
   */
  public function read(string $path): ?string;
  
  /**
   * @return int
   */
  public function clearCache(): int;

  /**
   * @param string $fileName
   * @return bool
   */
  public function existsInCache(string $fileName): bool;

  /**
   * @param string $fileName
   * @param string $content
   * @return void
   */
  public function writeToCache(string $fileName, string $content): void;

  /**
   * @param string $fileName
   * @return string|null
   */
  public function readFromCache(string $fileName): ?string;

  /**
   * @param string $fileName
   * @return void
   */
  public function deleteFromCache(string $fileName): void;
  
  /**
   * @return string
   */
  public static function getAlias(): string;
}