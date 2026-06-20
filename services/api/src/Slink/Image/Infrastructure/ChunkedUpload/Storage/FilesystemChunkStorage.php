<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ChunkedUpload\Storage;

use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Infrastructure\Exception\Storage\LocalStorageException;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(ChunkStorageInterface::class)]
final class FilesystemChunkStorage extends AbstractChunkStorage {
  /**
   * @param ConfigurationProviderInterface<SettingsService> $configurationProvider
   */
  public function __construct(
    private readonly ConfigurationProviderInterface $configurationProvider,
  ) {
  }

  public function writeChunk(string $uploadId, int $index, string $content): void {
    $directory = $this->uploadDirectory($uploadId);

    $this->ensureDirectory($directory);

    \file_put_contents($this->chunkPath($uploadId, $index), $content);
  }

  public function readChunk(string $uploadId, int $index): ?string {
    $path = $this->chunkPath($uploadId, $index);

    if (!\is_file($path)) {
      return null;
    }

    $content = \file_get_contents($path);

    return $content === false ? null : $content;
  }

  /**
   * @return array<int>
   */
  public function listChunkIndexes(string $uploadId): array {
    $directory = $this->uploadDirectory($uploadId);

    if (!\is_dir($directory)) {
      return [];
    }

    $entries = \scandir($directory);

    if ($entries === false) {
      return [];
    }

    $indexes = [];

    foreach ($entries as $entry) {
      if (\ctype_digit($entry)) {
        $indexes[] = (int) $entry;
      }
    }

    \sort($indexes);

    return $indexes;
  }

  /**
   * @return array<string>
   */
  public function listUploadIds(): array {
    $root = $this->chunksRootPath();

    if (!\is_dir($root)) {
      return [];
    }

    $entries = \scandir($root);

    if ($entries === false) {
      return [];
    }

    $uploadIds = [];

    foreach ($entries as $entry) {
      if ($entry === '.' || $entry === '..') {
        continue;
      }

      if (\is_dir($root . '/' . $entry)) {
        $uploadIds[] = $entry;
      }
    }

    return $uploadIds;
  }

  public function lastModified(string $uploadId): ?int {
    $directory = $this->uploadDirectory($uploadId);

    if (!\is_dir($directory)) {
      return null;
    }

    $mtime = \filemtime($directory);

    return $mtime === false ? null : $mtime;
  }

  public function markComplete(string $uploadId, string $imageId): void {
    $directory = $this->uploadDirectory($uploadId);

    $this->ensureDirectory($directory);

    \file_put_contents($this->markerPath($uploadId), $imageId);

    foreach ($this->listChunkIndexes($uploadId) as $index) {
      $path = $this->chunkPath($uploadId, $index);

      if (\is_file($path)) {
        \unlink($path);
      }
    }
  }

  public function findCompletedImageId(string $uploadId): ?string {
    $path = $this->markerPath($uploadId);

    if (!\is_file($path)) {
      return null;
    }

    $content = \file_get_contents($path);

    return $content === false ? null : $content;
  }

  public function deleteUpload(string $uploadId): void {
    $directory = $this->uploadDirectory($uploadId);

    if (!\is_dir($directory)) {
      return;
    }

    $files = \glob($directory . '/{,.}[!.]*', GLOB_BRACE);

    if ($files !== false) {
      foreach ($files as $file) {
        if (\is_file($file)) {
          \unlink($file);
        }
      }
    }

    \rmdir($directory);
  }

  private function ensureDirectory(string $path): void {
    if (!\is_dir($path) && !@\mkdir($path, 0755, true) && !\is_dir($path)) {
      throw LocalStorageException::unableToCreateDirectory($path);
    }
  }

  private function serverRoot(): string {
    return (string) $this->configurationProvider->get('storage.adapter.local.dir');
  }

  private function chunksRootPath(): string {
    return $this->serverRoot() . '/' . $this->chunksRoot();
  }

  private function uploadDirectory(string $uploadId): string {
    return $this->serverRoot() . '/' . $this->prefix($uploadId);
  }

  private function chunkPath(string $uploadId, int $index): string {
    return $this->uploadDirectory($uploadId) . '/' . $index;
  }

  private function markerPath(string $uploadId): string {
    return $this->uploadDirectory($uploadId) . '/' . self::COMPLETION_MARKER;
  }
}
