<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem\Storage;

use Slink\Settings\Domain\Service\ConfigurationProvider;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\File\File;

final class LocalStorage extends AbstractStorage {
  
  /**
   * @param string $projectPublicDir
   */
  private function __construct(string $projectPublicDir) {
    $this->setServerRoot($projectPublicDir);
  }
  
  /**
   * @param ConfigurationProvider $configurationProvider
   * @return static
   */
  #[\Override]
  static function create(ConfigurationProvider $configurationProvider): self {
    $config = $configurationProvider->get('storage.provider.local');
    return new self($config['dir']);
  }
  
  /**
   * @param File $file
   * @param string $fileName
   * @return void
   */
  public function upload(File $file, string $fileName): void {
    $file->move($this->getPath(), $fileName);
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
    $path = $this->getPath() . '/' . $fileName;
    
    unlink($path);
  }
  
  public function exists(string $path): bool {
    return file_exists($path);
  }
  
  public function mkdir(string $path): void {
    mkdir($path, 0777, true);
  }
}