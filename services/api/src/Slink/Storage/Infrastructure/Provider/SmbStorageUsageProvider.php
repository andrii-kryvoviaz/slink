<?php

declare(strict_types=1);

namespace Slink\Storage\Infrastructure\Provider;

use Icewind\SMB\BasicAuth;
use Icewind\SMB\Exception\DependencyException;
use Icewind\SMB\Exception\NotFoundException;
use Icewind\SMB\IShare;
use Icewind\SMB\ServerFactory;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\Enum\StorageProvider;
use Slink\Storage\Domain\Exception\SmbConfigurationIncompleteException;
use Slink\Storage\Domain\Service\StorageUsageProviderInterface;
use Slink\Storage\Domain\ValueObject\StorageUsage;

final readonly class SmbStorageUsageProvider implements StorageUsageProviderInterface {
  public function __construct(
    private ConfigurationProviderInterface $configurationProvider
  ) {
  }
  
  public function getUsage(): StorageUsage {
    $config = $this->configurationProvider->get('storage.adapter.smb');
    
    if (!$config || !isset($config['host'], $config['share'], $config['username'])) {
      throw new SmbConfigurationIncompleteException();
    }
    
    try {
      $share = $this->createSmbShare($config);
      $slinkPath = $this->configurationProvider->get('storage.adapter.path') ?? 'slink';
      
      if (!$this->directoryExists($share, $slinkPath)) {
        return new StorageUsage(
          provider: StorageProvider::SmbShare->value,
          usedBytes: 0,
          totalBytes: null,
          fileCount: 0
        );
      }
      
      [$usedBytes, $fileCount] = $this->calculateDirectoryUsage($share, $slinkPath);
      
      return new StorageUsage(
        provider: StorageProvider::SmbShare->value,
        usedBytes: $usedBytes,
        totalBytes: null,
        fileCount: $fileCount
      );
    } catch (DependencyException $e) {
      throw new SmbConfigurationIncompleteException('SMB client dependency error: ' . $e->getMessage());
    }
  }
  public function supports(StorageProvider $provider): bool {
    return StorageProvider::SmbShare->equals($provider);
  }
  
  public static function getAlias(): string {
    return StorageProvider::SmbShare->value;
  }
  
  /**
   * @param array<string, mixed> $config
   */
  private function createSmbShare(array $config): IShare {
    $basicAuth = new BasicAuth(
      username: $config['username'],
      workgroup: $config['workgroup'] ?? 'workgroup',
      password: $config['password']
    );
    
    $smbClientServer = (new ServerFactory())->createServer($config['host'], $basicAuth);
    return $smbClientServer->getShare($config['share']);
  }
  
  private function directoryExists(IShare $share, string $path): bool {
    try {
      $share->stat($path);
      return true;
    } catch (NotFoundException) {
      return false;
    }
  }
  
  /**
   * @return array{int, int}
   */
  private function calculateDirectoryUsage(IShare $share, string $path): array {
    $totalSize = 0;
    $fileCount = 0;
    
    $this->traverseDirectory($share, $path, $totalSize, $fileCount);
    
    return [$totalSize, $fileCount];
  }
  
  private function traverseDirectory(IShare $share, string $path, int &$totalSize, int &$fileCount): void {
    try {
      $contents = $share->dir($path);
      
      foreach ($contents as $item) {
        if ($item->getName() === '.' || $item->getName() === '..') {
          continue;
        }
        
        $itemPath = rtrim($path, '/') . '/' . $item->getName();
        
        if ($item->isDirectory()) {
          $this->traverseDirectory($share, $itemPath, $totalSize, $fileCount);
        } else {
          $totalSize += $item->getSize();
          $fileCount++;
        }
      }
    } catch (NotFoundException) {
    }
  }
}
