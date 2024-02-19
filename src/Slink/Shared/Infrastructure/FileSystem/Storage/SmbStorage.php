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
use Slink\Settings\Domain\Service\ConfigurationProvider;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Symfony\Component\HttpFoundation\File\File;

final class SmbStorage extends AbstractStorage {
  
  public function __construct(private readonly IShare $share) {
  }
  
  /**
   * @param ConfigurationProvider $configurationProvider
   * @return self
   * @throws DependencyException
   */
  #[\Override]
  static function create(ConfigurationProvider $configurationProvider): self {
    $config = $configurationProvider->get('storage.provider.smb');
    [$host, $share, $workgroup, $username, $password] = array_values($config);
    
    $basicAuth = new BasicAuth(
      username: $username,
      workgroup: $workgroup ?? 'workgroup',
      password: $password,
    );
    
    $smbClientServer = (new ServerFactory())->createServer($host, $basicAuth);
    return new self($smbClientServer->getShare($share));
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
        $this->mkdir($carry);
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
  public function write(string $path, string $content): void {
    $stream = $this->share->write($path);
    
    fwrite($stream, $content);
  }
  
  /**
   * @throws NotFoundException
   * @throws InvalidTypeException
   */
  public function read(string $path): ?string {
    $stream = $this->share->read($path);
    $content = stream_get_contents($stream);
    return $content === false ? null : $content;
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
}