<?php

declare(strict_types=1);

namespace Tests\Integration\Storage;

use Icewind\SMB\Native\NativeServer;
use Icewind\SMB\System;
use Icewind\SMB\Wrapped\Server;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Infrastructure\FileSystem\Storage\AbstractStorage;
use Slink\Shared\Infrastructure\FileSystem\Storage\SmbStorage;
use Symfony\Component\HttpFoundation\File\File;

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
    return new SmbStorage(self::smbConfigurationProvider());
  }

  #[Test]
  public function itUploadsToAFreshUnpreparedNestedPrefix(): void {
    $freshImageDir = 'images-fresh-' . bin2hex(random_bytes(6));
    $storage = self::createStorageWithImageDir($freshImageDir);

    self::assertFalse(
      $storage->exists('slink/' . $freshImageDir),
      'precondition: the fresh nested prefix slink/' . $freshImageDir . ' must not exist before upload',
    );

    $fileName = 'fresh-prefix-' . bin2hex(random_bytes(4)) . '.jpg';
    $imagePath = 'slink/' . $freshImageDir . '/' . $fileName;

    try {
      $storage->upload(new File(self::sampleFixturePath()), $fileName);

      self::assertTrue(
        $storage->exists($imagePath),
        'upload() must create the fresh nested prefix and store the file at ' . $imagePath,
      );
      self::assertSame(
        (string) file_get_contents(self::sampleFixturePath()),
        $storage->readImage($fileName),
        'readImage() must return the exact fixture bytes stored under the fresh prefix',
      );
    } finally {
      try {
        $storage->delete($fileName);
      } catch (\Throwable) {
      }
    }
  }

  private static function smbConfigurationProvider(): ConfigurationProviderInterface {
    return self::createConfigurationProvider([
      'storage.adapter.smb' => [
        'host' => StorageTestEnvironment::smbHost(),
        'share' => StorageTestEnvironment::smbShare(),
        'username' => StorageTestEnvironment::smbUsername(),
        'password' => StorageTestEnvironment::smbPassword(),
        'workgroup' => StorageTestEnvironment::smbWorkgroup(),
      ],
    ]);
  }

  private static function createStorageWithImageDir(string $imageDir): SmbStorage {
    $storage = new SmbStorage(self::smbConfigurationProvider());

    $imageDirProperty = new \ReflectionProperty(AbstractStorage::class, 'imageDir');
    $imageDirProperty->setValue($storage, $imageDir);

    return $storage;
  }

  private static function sampleFixturePath(): string {
    return \dirname(__DIR__, 2) . '/fixtures/test.jpg';
  }
}
