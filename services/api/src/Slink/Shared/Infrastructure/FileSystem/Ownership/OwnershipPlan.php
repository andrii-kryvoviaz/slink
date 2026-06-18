<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem\Ownership;

final readonly class OwnershipPlan {
  /**
   * @param list<OwnershipEntry> $entries
   */
  private function __construct(
    private array $entries,
  ) {
  }

  public static function fromStoragePaths(string $appDir, string $apiVarDir, string $dataDir, string $runDir): self {
    return new self([
      new OwnershipEntry(path: $appDir, owner: 'slink', group: 'slink', recursive: true),
      new OwnershipEntry(path: $appDir . '/var/data', owner: 'www-data', group: 'slink'),
      new OwnershipEntry(path: $apiVarDir, owner: 'www-data', group: 'slink', recursive: true),
      new OwnershipEntry(path: $appDir . '/var/data/*.db', owner: 'www-data', group: 'www-data', optional: true, glob: true),
      new OwnershipEntry(path: $dataDir . '/caddy', owner: 'www-data', group: 'slink', recursive: true),
      new OwnershipEntry(path: $dataDir . '/redis', owner: 'redis', group: 'slink', recursive: true),
      new OwnershipEntry(path: $appDir . '/var/data', mode: 0o770),
      new OwnershipEntry(path: $appDir . '/slink/images', mode: 0o770),
      new OwnershipEntry(path: $appDir . '/slink/cache', mode: 0o770, optional: true),
      new OwnershipEntry(path: $appDir . '/var/data/keys', mode: 0o750),
      new OwnershipEntry(path: $appDir . '/var/data/keys/private.pem', mode: 0o640, optional: true),
      new OwnershipEntry(path: $appDir . '/var/data/keys/passphrase', mode: 0o640, optional: true),
      new OwnershipEntry(path: $runDir, owner: 'root', group: 'root'),
    ]);
  }

  /**
   * @return list<OwnershipEntry>
   */
  public function getEntries(): array {
    return $this->entries;
  }
}
