<?php

declare(strict_types=1);

namespace Slink\Storage\Infrastructure\Provider;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\Enum\StorageProvider;
use Slink\Storage\Domain\Exception\StorageDirectoryNotFoundException;
use Slink\Storage\Domain\Service\StorageUsageProviderInterface;
use Slink\Storage\Domain\ValueObject\StorageUsage;

final readonly class LocalStorageUsageProvider implements StorageUsageProviderInterface {
  public function __construct(
    private ConfigurationProviderInterface $configurationProvider
  ) {
  }
  
  public function getUsage(): StorageUsage {
    $storageDir = $this->configurationProvider->get('storage.adapter.local.dir');
    
    if (!$storageDir || !is_dir($storageDir)) {
      throw new StorageDirectoryNotFoundException($storageDir ?? 'unknown');
    }
    
    $slinkDir = rtrim($storageDir, '/') . '/slink';
    
    if (!is_dir($slinkDir)) {
      return new StorageUsage(
        provider: StorageProvider::Local->value,
        usedBytes: 0,
        totalBytes: $this->getDiskSpace($storageDir),
        fileCount: 0,
        cacheBytes: 0,
        cacheFileCount: 0
      );
    }
    
    $imagesDir = $slinkDir . '/images';
    $cacheDir = $slinkDir . '/cache';
    
    $usedBytes = $this->getDirectorySize($slinkDir);
    $fileCount = $this->getFileCount($slinkDir);
    $totalBytes = $this->getDiskSpace($storageDir);
    
    $cacheBytes = is_dir($cacheDir) ? $this->getDirectorySize($cacheDir) : 0;
    $cacheFileCount = is_dir($cacheDir) ? $this->getFileCount($cacheDir) : 0;
    
    return new StorageUsage(
      provider: StorageProvider::Local->value,
      usedBytes: $usedBytes,
      totalBytes: $totalBytes,
      fileCount: $fileCount,
      cacheBytes: $cacheBytes,
      cacheFileCount: $cacheFileCount
    );
  }
  
  public function supports(StorageProvider $provider): bool {
    return StorageProvider::Local->equals($provider);
  }
  
  public static function getAlias(): string {
    return StorageProvider::Local->value;
  }
  
  private function getDirectorySize(string $directory): int {
    $size = 0;
    $iterator = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
      if ($file->isFile()) {
        $size += $file->getSize();
      }
    }
    
    return $size;
  }
  
  private function getFileCount(string $directory): int {
    $count = 0;
    $iterator = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
      if ($file->isFile()) {
        $count++;
      }
    }
    
    return $count;
  }
  
  private function getDiskSpace(string $directory): ?int {
    $totalBytes = disk_total_space($directory);
    return $totalBytes !== false ? (int) $totalBytes : null;
  }
}
