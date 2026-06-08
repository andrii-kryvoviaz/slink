<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem\Storage\Contract;

use Slink\Shared\Infrastructure\FileSystem\FileSource;
use Slink\Shared\Infrastructure\FileSystem\FileStream;
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
   * @param string $fileName
   * @return string|null
   */
  public function readImage(string $fileName): ?string;

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
   * @param string $fileName
   * @return FileStream
   */
  public function readStream(string $fileName): FileStream;

  /**
   * @param string $fileName
   * @return FileSource
   */
  public function readSource(string $fileName): FileSource;

  /**
   * @param string $fileName
   * @return string
   */
  public function cachePath(string $fileName): string;

  /**
   * @return string
   */
  public static function getAlias(): string;
}
