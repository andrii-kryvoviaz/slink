<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem\Storage;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\Enum\StorageProvider;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\DirectoryStorageInterface;
use Symfony\Component\HttpFoundation\File\File;

final class LocalStorage extends AbstractStorage implements DirectoryStorageInterface {
  
  /**
   * @param ConfigurationProviderInterface $configurationProvider
   * @return void
   */
  #[\Override]
  function init(ConfigurationProviderInterface $configurationProvider): void {
    $this->setServerRoot($configurationProvider->get('storage.adapter.local.dir'));
  }
  
  /**
   * @param File $file
   * @param string $fileName
   * @return void
   * @throws NotFoundException
   */
  public function upload(File $file, string $fileName): void {
    $path = $this->getPath();
    
    if (!$path) {
      throw new NotFoundException();
    }
    
    if (!is_dir($path)) {
      $this->mkdir($path);
    }
    
    $file->move($path, $fileName);
  }
  
  public function write(string $path, string $content): void {
    file_put_contents($path, $content);
  }
  
  /**
   * @throws NotFoundException
   */
  public function read(string $path): ?string {
    try {
      $content = file_get_contents($path);
      return $content === false ? null : $content;
    } catch (\Exception $e) {
      throw new NotFoundException();
    }
  }
  
  public function delete(string $fileName): void {
    $imagePath = $this->getPath() . '/' . $fileName;
    
    if (file_exists($imagePath)) {
      unlink($imagePath);
    }
    
    [$name, $_] = explode('.', $fileName);
    $this->deleteCacheFiles($name);
  }
  
  private function deleteCacheFiles(string $prefix): void {
    $cachePath = $this->getPath(isCache: true);
    
    if (!$cachePath || !is_dir($cachePath)) {
      return;
    }
    
    $files = glob($cachePath . '/' . $prefix . '-*');
    
    if ($files === false) {
      return;
    }
    
    foreach ($files as $file) {
      if (is_file($file)) {
        unlink($file);
      }
    }
  }
  
  public function exists(string $path): bool {
    return file_exists($path);
  }
  
  public function mkdir(string $path): void {
    mkdir($path, 0755, true);
  }
  
  /**
   * @return string
   */
  public static function getAlias(): string {
    return StorageProvider::Local->value;
  }
}