<?php

declare(strict_types=1);

namespace Slink\Storage\Domain\ValueObject;

final readonly class StorageUsage {
  public function __construct(
    private string $provider,
    private int $usedBytes,
    private ?int $totalBytes = null,
    private int $fileCount = 0,
    private int $cacheBytes = 0,
    private int $cacheFileCount = 0
  ) {
  }
  
  public function getProvider(): string {
    return $this->provider;
  }
  
  public function getUsedBytes(): int {
    return $this->usedBytes;
  }
  
  public function getTotalBytes(): ?int {
    return $this->totalBytes;
  }
  
  public function getFileCount(): int {
    return $this->fileCount;
  }
  
  public function getCacheBytes(): int {
    return $this->cacheBytes;
  }
  
  public function getCacheFileCount(): int {
    return $this->cacheFileCount;
  }
  
  public function getUsagePercentage(): ?float {
    if ($this->totalBytes === null || $this->totalBytes === 0) {
      return null;
    }
    
    return ($this->usedBytes / $this->totalBytes) * 100;
  }
  
  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'provider' => $this->provider,
      'usedBytes' => $this->usedBytes,
      'totalBytes' => $this->totalBytes,
      'fileCount' => $this->fileCount,
      'usagePercentage' => $this->getUsagePercentage(),
      'cacheBytes' => $this->cacheBytes,
      'cacheFileCount' => $this->cacheFileCount,
    ];
  }
}