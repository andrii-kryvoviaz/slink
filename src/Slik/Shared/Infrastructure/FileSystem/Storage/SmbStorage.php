<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\FileSystem\Storage;

use Icewind\SMB\BasicAuth;
use Icewind\SMB\Exception\AlreadyExistsException;
use Icewind\SMB\Exception\DependencyException;
use Icewind\SMB\Exception\InvalidTypeException;
use Icewind\SMB\Exception\NotFoundException;
use Icewind\SMB\IShare;
use Icewind\SMB\ServerFactory;
use Symfony\Component\HttpFoundation\File\File;

final class SmbStorage implements StorageInterface {
  private IShare $share;
  
  private string $directory = self::PUBLIC_PATH;
  
  /**
   * @throws DependencyException
   */
  public function __construct(string $host, string $username, string $password, string $share) {
    $serverFactory = new ServerFactory();
    $auth = new BasicAuth($username, 'workgroup', $password);
    $server = $serverFactory->createServer($host, $auth);
    
    $this->share = $server->getShare($share);
  }
  
  /**
   * @throws NotFoundException
   * @throws AlreadyExistsException
   * @throws InvalidTypeException
   */
  public function upload(File $file, string $fileName): void {
    $path = $this->directory ?? '';
    
    if(!$this->dirExists($path)) {
      $this->share->mkdir($path);
    }
    
    $this->share->put($file->getPathname(), $this->getPath($fileName));
  }
  
  public function getPath(string $fileName): string {
    $path = $this->directory ?? '';
    
    return $path . '/' . $fileName;
  }
  
  /**
   * @throws NotFoundException
   * @throws InvalidTypeException
   */
  public function delete(string $fileName): void {
    $this->share->del($this->getPath($fileName));
  }
  
  public function dirExists(string $path): bool {
    try {
      $this->share->stat($path);
      
      return true;
    } catch (NotFoundException) {
      return false;
    }
  }
  
  public function exists(string $fileName): bool {
    try {
      $this->share->stat($this->getPath($fileName));
      
      return true;
    } catch (NotFoundException) {
      return false;
    }
  }
  
  /**
   * @throws NotFoundException
   * @throws InvalidTypeException
   */
  public function getImageContent(string $fileName): string {
    $stream = $this->share->read($this->getPath($fileName));
    
    return stream_get_contents($stream);
  }
}