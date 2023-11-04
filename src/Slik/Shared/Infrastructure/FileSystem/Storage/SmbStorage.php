<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\FileSystem\Storage;

use Icewind\SMB\Exception\InvalidTypeException;
use Icewind\SMB\Exception\AlreadyExistsException;
use Icewind\SMB\Exception\NotFoundException;
use Icewind\SMB\IShare;
use Slik\Shared\Domain\ValueObject\ImageOptions;
use Symfony\Component\HttpFoundation\File\File;

final class SmbStorage extends AbstractStorage {
  
  public function __construct(private readonly IShare $share) {
  }
  
  /**
   * @throws NotFoundException
   * @throws AlreadyExistsException
   * @throws InvalidTypeException
   */
  public function upload(File $file, ImageOptions|string $image): void {
    $path = $this->getAbsolutePath($image, onlyDir: true);
    $parts = explode('/', $path);
    
    array_reduce($parts, function ($carry, $item) {
      $carry .= $item . '/';
      
      if(!$this->dirExists($carry)) {
        $this->share->mkdir($carry);
      }
      
      return $carry;
    }, '');
    
    $fullPath = $this->getAbsolutePath($image);
    
    $this->share->put($file->getPathname(), $fullPath);
  }
  
  /**
   * @throws NotFoundException
   * @throws InvalidTypeException
   */
  public function getImageContent(ImageOptions|string $image): string {
    $path = $this->getAbsolutePath($image);
    
    $stream = $this->share->read($path);
    
    return stream_get_contents($stream);
  }
  
  /**
   * @throws NotFoundException
   * @throws InvalidTypeException
   */
  public function delete(ImageOptions|string $image): void {
    $path = $this->getAbsolutePath($image);
    
    $this->share->del($path);
  }
  
  public function dirExists(string $path): bool {
    try {
      $this->share->stat($path);
      
      return true;
    } catch (NotFoundException) {
      return false;
    }
  }
  
  public function exists(ImageOptions|string $image): bool {
    $path = $this->getAbsolutePath($image);
    
    try {
      $this->share->stat($path);
      
      return true;
    } catch (NotFoundException) {
      return false;
    }
  }
}