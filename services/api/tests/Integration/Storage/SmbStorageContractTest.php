<?php

declare(strict_types=1);

namespace Tests\Integration\Storage;

use Icewind\SMB\Native\NativeServer;
use Icewind\SMB\System;
use Icewind\SMB\Wrapped\Server;
use PHPUnit\Framework\Attributes\Group;
use Slink\Shared\Infrastructure\FileSystem\Storage\AbstractStorage;
use Slink\Shared\Infrastructure\FileSystem\Storage\SmbStorage;

#[Group('storage-integration')]
final class SmbStorageContractTest extends StorageContractTestCase {
  private const int SMB_PORT = 445;

  #[\Override]
  protected static function backendName(): string {
    return 'SMB';
  }

  #[\Override]
  protected static function ensureClientToolingAvailable(): void {
    $system = new System();

    if (NativeServer::available($system) || Server::available($system)) {
      return;
    }

    StorageTestEnvironment::abort('No SMB client backend is available, install the smbclient binary or the php-smbclient extension.');
  }

  #[\Override]
  protected static function probeBackend(): bool {
    return StorageTestEnvironment::probeTcp(StorageTestEnvironment::smbHost(), self::SMB_PORT);
  }

  #[\Override]
  protected static function createStorage(): AbstractStorage {
    return new SmbStorage(self::createConfigurationProvider([
      'storage.adapter.smb' => [
        'host' => StorageTestEnvironment::smbHost(),
        'share' => StorageTestEnvironment::smbShare(),
        'username' => StorageTestEnvironment::smbUsername(),
        'password' => StorageTestEnvironment::smbPassword(),
      ],
    ]));
  }
}
