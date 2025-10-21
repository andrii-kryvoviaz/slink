<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem\Storage;

use Icewind\SMB\BasicAuth;
use Icewind\SMB\Exception\DependencyException;
use Icewind\SMB\Exception\InvalidTypeException;
use Icewind\SMB\Exception\AlreadyExistsException;
use Icewind\SMB\Exception\NotFoundException;
use Icewind\SMB\IShare;
use Icewind\SMB\ServerFactory;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\Enum\StorageProvider;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\DirectoryStorageInterface;
use Symfony\Component\HttpFoundation\File\File;

final class SmbStorage extends AbstractStorage implements DirectoryStorageInterface {
  private IShare $share;
  
  /**
   * @param ConfigurationProviderInterface $configurationProvider
   * @return void
   * @throws DependencyException
   */
  #[\Override]
  function init(ConfigurationProviderInterface $configurationProvider): void {
    $config = $configurationProvider->get('storage.adapter.smb');
    
    [
      'host' => $host,
      'share' => $share,
      'username' => $username,
      'password' => $password
    ] = $config;
    
    $workgroup = $config['workgroup'] ?? 'workgroup';
    
    $basicAuth = new BasicAuth(
      username: $username,
      workgroup: $workgroup,
      password: $password,
    );
    
    $smbClientServer = new ServerFactory()->createServer($host, $basicAuth);
    $this->share = $smbClientServer->getShare($share);
  }
  
  /**
   * @throws NotFoundException
   * @throws AlreadyExistsException
   * @throws InvalidTypeException
   */
  public function upload(File $file, string $fileName): void {
    $path = $this->getPath() ?? '';
    $parts = explode('/', $path);
    
    array_reduce($parts, function ($carry, $item) {
      $carry .= $item . '/';
      
      if(!$this->dirExists($carry)) {
        $this->mkdir($carry);
      }
      
      return $carry;
    }, '');
    
    $fullPath = implode('/', [ $path, $fileName ]);
    
    $this->share->put($file->getPathname(), $fullPath);
  }
  
  /**
   * @throws NotFoundException
   * @throws InvalidTypeException
   */
  public function write(string $path, string $content): void {
    $stream = $this->share->write($path);
    
    fwrite($stream, $content);
  }
  
  /**
   * @throws InvalidTypeException
   */
  public function read(string $path): ?string {
    try {
      $stream = $this->share->read($path);
      $content = stream_get_contents($stream);
      return $content === false ? null : $content;
    } catch (NotFoundException) {
      return null;
    }
  }
  
  /**
   * @throws NotFoundException
   * @throws InvalidTypeException
   */
  public function delete(string $fileName): void {
    $path = implode('/', [ $this->getPath(), $fileName ]);
    
    try {
      $this->share->del($path);
    } catch (NotFoundException) {
    }
    
    [$name, $_] = explode('.', $fileName);
    $this->deleteCacheFiles($name);
  }
  
  private function deleteCacheFiles(string $prefix): void {
    $cachePath = $this->getPath(isCache: true);
    
    if (!$cachePath) {
      return;
    }
    
    try {
      $contents = $this->share->dir($cachePath);
      
      foreach ($contents as $file) {
        if ($file->isDirectory()) {
          continue;
        }
        
        $fileName = $file->getName();
        
        if (str_starts_with($fileName, $prefix . '-')) {
          try {
            $this->share->del($cachePath . '/' . $fileName);
          } catch (NotFoundException) {
          }
        }
      }
    } catch (NotFoundException) {
    }
  }
  
  public function dirExists(string $path): bool {
    try {
      $this->share->stat($path);
      
      return true;
    } catch (NotFoundException) {
      return false;
    }
  }
  
  public function exists(string $path): bool {
    try {
      $this->share->stat($path);
      
      return true;
    } catch (NotFoundException) {
      return false;
    }
  }
  
  /**
   * @throws NotFoundException
   * @throws AlreadyExistsException
   */
  public function mkdir(string $path): void {
    $this->share->mkdir($path);
  }
  
  /**
   * @return int
   */
  public function clearCache(): int {
    $cachePath = $this->getPath(isCache: true);
    
    if (!$cachePath) {
      return 0;
    }
    
    $count = 0;
    
    try {
      $contents = $this->share->dir($cachePath);
      
      foreach ($contents as $file) {
        if ($file->isDirectory()) {
          continue;
        }
        
        try {
          $this->share->del($cachePath . '/' . $file->getName());
          $count++;
        } catch (NotFoundException) {
        }
      }
    } catch (NotFoundException) {
    }
    
    return $count;
  }
  
  /**
   * @return string
   */
  public static function getAlias(): string {
    return StorageProvider::SmbShare->value;
  }
}