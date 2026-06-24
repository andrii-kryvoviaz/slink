<?php

declare(strict_types=1);

namespace Tests\Integration\Storage;

use Icewind\SMB\BasicAuth;
use Icewind\SMB\Exception\NotFoundException;
use Icewind\SMB\IShare;
use Icewind\SMB\Native\NativeServer;
use Icewind\SMB\ServerFactory;
use Icewind\SMB\System;
use Icewind\SMB\Wrapped\Server;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\Enum\StorageProvider;
use Slink\Shared\Infrastructure\FileSystem\Storage\SmbStorage;
use Slink\Storage\Domain\ValueObject\StorageUsage;
use Slink\Storage\Infrastructure\Provider\SmbStorageUsageProvider;

#[Group('storage-integration')]
final class SmbStorageUsageProviderContractTest extends TestCase {
  private const int SMB_PORT = 445;

  private static SmbStorage $storage;
  private static string $rootPath = '';

  /**
   * @var array<string, string>
   */
  private static array $imageFiles = [];

  /**
   * @var array<string, string>
   */
  private static array $cacheFiles = [];

  public static function setUpBeforeClass(): void {
    self::ensureClientToolingAvailable();
    self::ensureBackendReachable();

    self::$rootPath = 'slink-usage-' . bin2hex(random_bytes(8));
    self::$storage = self::createStorage();

    self::seedNestedTree();
  }

  public static function tearDownAfterClass(): void {
    if (self::$rootPath === '') {
      return;
    }

    $share = self::createRawShare();

    foreach ([...array_keys(self::$cacheFiles), ...array_keys(self::$imageFiles)] as $path) {
      self::deleteQuietly($share, $path);
    }

    foreach ([self::$rootPath . '/images/nested', self::$rootPath . '/images', self::$rootPath . '/cache', self::$rootPath] as $directory) {
      self::removeDirQuietly($share, $directory);
    }
  }

  #[Test]
  public function itReportsSeededImageAndCacheUsage(): void {
    $usage = $this->usageProvider(self::$rootPath)->getUsage();

    self::assertSame(StorageProvider::SmbShare->value, $usage->getProvider());

    self::assertSame(
      $this->totalBytesOf(self::$imageFiles) + $this->totalBytesOf(self::$cacheFiles),
      $usage->getUsedBytes(),
      'usedBytes must recursively sum every file under the slink path, images and cache combined.',
    );

    self::assertSame(
      \count(self::$imageFiles) + \count(self::$cacheFiles),
      $usage->getFileCount(),
      'fileCount must recursively count every file under the slink path, images and cache combined.',
    );

    self::assertSame(
      $this->totalBytesOf(self::$cacheFiles),
      $usage->getCacheBytes(),
      'cacheBytes must sum only the files under the cache subtree.',
    );

    self::assertSame(
      \count(self::$cacheFiles),
      $usage->getCacheFileCount(),
      'cacheFileCount must count only the files under the cache subtree.',
    );

    self::assertGreaterThan(0, $usage->getUsedBytes());
    self::assertGreaterThan(0, $usage->getCacheBytes());
  }

  #[Test]
  public function itReportsZeroUsageWhenSlinkPathDoesNotExist(): void {
    $missingPath = 'slink-usage-missing-' . bin2hex(random_bytes(8));

    $usage = $this->usageProvider($missingPath)->getUsage();

    self::assertSame(StorageProvider::SmbShare->value, $usage->getProvider());
    self::assertSame(0, $usage->getUsedBytes());
    self::assertSame(0, $usage->getFileCount());
    self::assertSame(0, $usage->getCacheBytes());
    self::assertSame(0, $usage->getCacheFileCount());
    self::assertNull($usage->getTotalBytes());
  }

  private function usageProvider(string $slinkPath): SmbStorageUsageProvider {
    return new SmbStorageUsageProvider(self::createConfigurationProvider([
      'storage.adapter.smb' => self::smbConfig(),
      'storage.adapter.path' => $slinkPath,
    ]));
  }

  /**
   * @param array<string, string> $files
   */
  private function totalBytesOf(array $files): int {
    return array_sum(array_map('strlen', $files));
  }

  private static function seedNestedTree(): void {
    $root = self::$rootPath;

    foreach ([$root, "$root/images", "$root/images/nested", "$root/cache"] as $directory) {
      if (!self::$storage->exists($directory)) {
        self::$storage->mkdir($directory);
      }
    }

    self::$imageFiles = [
      "$root/images/first.txt" => str_repeat('a', 11),
      "$root/images/second.txt" => str_repeat('b', 22),
      "$root/images/nested/third.txt" => str_repeat('c', 33),
    ];

    self::$cacheFiles = [
      "$root/cache/cached-one.txt" => str_repeat('x', 7),
      "$root/cache/cached-two.txt" => str_repeat('y', 13),
    ];

    foreach ([...self::$imageFiles, ...self::$cacheFiles] as $path => $content) {
      self::$storage->write($path, $content);
    }
  }

  /**
   * @return array<string, string>
   */
  private static function smbConfig(): array {
    return [
      'host' => StorageTestEnvironment::smbHost(),
      'share' => StorageTestEnvironment::smbShare(),
      'username' => StorageTestEnvironment::smbUsername(),
      'password' => StorageTestEnvironment::smbPassword(),
    ];
  }

  private static function createStorage(): SmbStorage {
    return new SmbStorage(self::createConfigurationProvider([
      'storage.adapter.smb' => self::smbConfig(),
    ]));
  }

  private static function createRawShare(): IShare {
    $config = self::smbConfig();

    $basicAuth = new BasicAuth(
      username: $config['username'],
      workgroup: 'workgroup',
      password: $config['password'],
    );

    $server = new ServerFactory()->createServer($config['host'], $basicAuth);

    return $server->getShare($config['share']);
  }

  private static function deleteQuietly(IShare $share, string $path): void {
    try {
      $share->del($path);
    } catch (NotFoundException) {
    }
  }

  private static function removeDirQuietly(IShare $share, string $path): void {
    try {
      $share->rmdir($path);
    } catch (NotFoundException) {
    }
  }

  private static function ensureClientToolingAvailable(): void {
    $system = new System();

    if (NativeServer::available($system) || Server::available($system)) {
      return;
    }

    StorageTestEnvironment::abort('No SMB client backend is available, install the smbclient binary or the php-smbclient extension.');
  }

  private static function ensureBackendReachable(): void {
    if (StorageTestEnvironment::probeTcp(StorageTestEnvironment::smbHost(), self::SMB_PORT)) {
      return;
    }

    StorageTestEnvironment::abortBackendNotReachable('SMB');
  }

  /**
   * @param array<string, mixed> $config
   */
  private static function createConfigurationProvider(array $config): ConfigurationProviderInterface {
    return new class($config) implements ConfigurationProviderInterface {
      /**
       * @param array<string, mixed> $config
       */
      public function __construct(private readonly array $config) {
      }

      public function get(string $key): mixed {
        return $this->config[$key] ?? null;
      }

      public function has(string $key): bool {
        return array_key_exists($key, $this->config);
      }

      /**
       * @return array<string, mixed>
       */
      public function all(): array {
        return $this->config;
      }
    };
  }
}
