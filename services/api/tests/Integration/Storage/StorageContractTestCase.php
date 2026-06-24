<?php

declare(strict_types=1);

namespace Tests\Integration\Storage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Infrastructure\FileSystem\Storage\AbstractStorage;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\DirectoryStorageInterface;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\ObjectStorageInterface;
use Symfony\Component\HttpFoundation\File\File;

abstract class StorageContractTestCase extends TestCase {
  private const string IMAGE_DIRECTORY = 'slink/images';
  private const string CACHE_DIRECTORY = 'slink/cache';

  private static ?AbstractStorage $storage = null;
  private static string $runPrefix = '';

  /**
   * @var list<string>
   */
  private static array $uploadedFileNames = [];

  abstract protected static function backendName(): string;

  abstract protected static function probeBackend(): bool;

  abstract protected static function createStorage(): AbstractStorage;

  protected static function ensureClientToolingAvailable(): void {
  }

  public static function setUpBeforeClass(): void {
    static::ensureClientToolingAvailable();
    self::ensureBackendReachable();

    self::$runPrefix = bin2hex(random_bytes(8));
    self::$uploadedFileNames = [];
    self::$storage = static::createStorage();

    self::prepareDirectories(self::$storage);
  }

  public static function tearDownAfterClass(): void {
    if (self::$storage === null) {
      return;
    }

    $failures = [];

    foreach (self::$uploadedFileNames as $fileName) {
      try {
        self::$storage->delete($fileName);
      } catch (\Throwable $failure) {
        $failures[] = $failure;
      }
    }

    try {
      self::$storage->clearCache();
    } catch (\Throwable $failure) {
      $failures[] = $failure;
    }

    self::$storage = null;

    if ($failures !== []) {
      throw $failures[0];
    }
  }

  #[Test]
  public function itUploadsAndReadsBackBinaryFixture(): void {
    $fileName = $this->uniqueFileName('fixture', 'jpg');

    $this->uploadFixture($fileName);

    self::assertSame(
      self::fixtureBytes(),
      $this->storage()->readImage($fileName),
      $this->describe('readImage must return the exact bytes uploaded as ' . $fileName),
    );
  }

  #[Test]
  public function itWritesAndReadsBackRawContent(): void {
    $fileName = $this->uniqueFileName('raw', 'txt');
    $path = $this->imagePath($fileName);
    $content = 'storage contract content for run ' . self::$runPrefix;

    $this->storage()->write($path, $content);
    self::$uploadedFileNames[] = $fileName;

    self::assertSame(
      $content,
      $this->storage()->read($path),
      $this->describe('read must return the exact content written to ' . $path),
    );
  }

  #[Test]
  public function itWritesAndReadsBackLargeMultiMegabyteContent(): void {
    $fileName = $this->uniqueFileName('large', 'bin');
    $path = $this->imagePath($fileName);
    $content = $this->deterministicBytes(3 * 1024 * 1024);

    $this->storage()->write($path, $content);
    self::$uploadedFileNames[] = $fileName;

    self::assertSame(
      $content,
      static::createStorage()->read($path),
      $this->describe('a second storage instance must read back the exact ' . \strlen($content) . ' bytes written to ' . $path),
    );
  }

  #[Test]
  public function itReportsExistenceOnlyForStoredFiles(): void {
    $fileName = $this->uniqueFileName('exists', 'txt');
    $path = $this->imagePath($fileName);

    $this->storage()->write($path, 'present');
    self::$uploadedFileNames[] = $fileName;

    self::assertTrue(
      $this->storage()->exists($path),
      $this->describe('exists must report true for stored path ' . $path),
    );
    self::assertFalse(
      $this->storage()->exists($this->imagePath($this->uniqueFileName('never-stored', 'txt'))),
      $this->describe('exists must report false for a path that was never stored'),
    );
  }

  #[Test]
  public function itDeletesUploadedFile(): void {
    $fileName = $this->uniqueFileName('delete', 'jpg');
    $path = $this->imagePath($fileName);

    $this->uploadFixture($fileName);

    self::assertTrue(
      $this->storage()->exists($path),
      $this->describe('uploaded file must exist at ' . $path . ' before deletion'),
    );

    $this->storage()->delete($fileName);

    self::assertFalse(
      $this->storage()->exists($path),
      $this->describe('deleted file must no longer exist at ' . $path),
    );
  }

  #[Test]
  public function itOverwritesExistingPathWithNewContent(): void {
    $fileName = $this->uniqueFileName('overwrite', 'txt');
    $path = $this->imagePath($fileName);

    $this->storage()->write($path, 'first content version, intentionally longer');
    self::$uploadedFileNames[] = $fileName;

    $this->storage()->write($path, 'second');

    self::assertSame(
      'second',
      $this->storage()->read($path),
      $this->describe('overwriting ' . $path . ' must fully replace the previous content'),
    );
  }

  #[Test]
  public function itStreamsUploadedFileWithIdenticalBytes(): void {
    $fileName = $this->uniqueFileName('stream', 'jpg');

    $this->uploadFixture($fileName);

    $stream = $this->storage()->readStream($fileName);

    self::assertSame(
      self::fixtureBytes(),
      stream_get_contents($stream->resource()),
      $this->describe('readStream must yield the exact bytes uploaded as ' . $fileName),
    );
  }

  #[Test]
  public function itHandlesFileNamesWithSpacesAndUnicode(): void {
    $fileName = self::$runPrefix . '-üñíçødé image å.jpg';

    $this->uploadFixture($fileName);

    self::assertSame(
      self::fixtureBytes(),
      $this->storage()->readImage($fileName),
      $this->describe('readImage must roundtrip a file name with spaces and unicode: ' . $fileName),
    );

    $this->storage()->delete($fileName);

    self::assertFalse(
      $this->storage()->exists($this->imagePath($fileName)),
      $this->describe('delete must remove a file name with spaces and unicode: ' . $fileName),
    );
  }

  #[Test]
  public function itWritesAndReadsCacheEntriesOnRemoteBackend(): void {
    $fileName = $this->uniqueFileName('cached', 'txt');
    $content = 'cached content for run ' . self::$runPrefix;

    $this->storage()->writeToCache($fileName, $content);

    self::assertTrue(
      $this->storage()->existsInCache($fileName),
      $this->describe('existsInCache must report true after writeToCache for ' . $fileName),
    );
    self::assertSame(
      $content,
      $this->storage()->readFromCache($fileName),
      $this->describe('readFromCache must return the exact content cached as ' . $fileName),
    );
    self::assertNull(
      $this->storage()->readFromCache($this->uniqueFileName('never-cached', 'txt')),
      $this->describe('readFromCache must return null for an entry that was never cached'),
    );
  }

  #[Test]
  public function itDeletesIndividualCacheEntryOnRemoteBackend(): void {
    $deleted = $this->uniqueFileName('cache-delete', 'txt');
    $kept = $this->uniqueFileName('cache-keep', 'txt');

    $this->storage()->writeToCache($deleted, 'cache entry to delete');
    $this->storage()->writeToCache($kept, 'cache entry to keep');

    $this->storage()->deleteFromCache($deleted);

    self::assertFalse(
      $this->storage()->existsInCache($deleted),
      $this->describe('deleteFromCache must remove cache entry ' . $deleted),
    );
    self::assertTrue(
      $this->storage()->existsInCache($kept),
      $this->describe('deleteFromCache must not remove unrelated cache entry ' . $kept),
    );
  }

  #[Test]
  public function itClearsRemoteCacheEntries(): void {
    $first = $this->uniqueFileName('clear-one', 'txt');
    $second = $this->uniqueFileName('clear-two', 'txt');

    $this->storage()->writeToCache($first, 'one');
    $this->storage()->writeToCache($second, 'two');

    $this->storage()->clearCache();

    self::assertFalse(
      $this->storage()->existsInCache($first),
      $this->describe('clearCache must remove cache entry ' . $first),
    );
    self::assertFalse(
      $this->storage()->existsInCache($second),
      $this->describe('clearCache must remove cache entry ' . $second),
    );
  }

  #[Test]
  public function itCreatesDirectoriesWhenSupported(): void {
    $storage = $this->storage();

    if (!$storage instanceof DirectoryStorageInterface) {
      self::markTestSkipped(static::backendName() . ' storage does not expose directory operations.');
    }

    $directory = 'slink/' . self::$runPrefix . '-directory';

    $storage->mkdir($directory);

    self::assertTrue(
      $storage->exists($directory),
      $this->describe('mkdir must create directory ' . $directory),
    );
  }

  protected function storage(): AbstractStorage {
    if (self::$storage === null) {
      self::fail('Storage backend was not initialized for ' . static::backendName() . ' contract tests.');
    }

    return self::$storage;
  }

  protected function imagePath(string $fileName): string {
    if ($this->storage() instanceof ObjectStorageInterface) {
      return $fileName;
    }

    return self::IMAGE_DIRECTORY . '/' . $fileName;
  }

  protected function uniqueFileName(string $label, string $extension): string {
    return sprintf('%s-%s.%s', self::$runPrefix, $label, $extension);
  }

  protected function uploadFixture(string $fileName): void {
    $this->storage()->upload(new File(self::fixturePath()), $fileName);
    self::$uploadedFileNames[] = $fileName;
  }

  protected function describe(string $message): string {
    return sprintf('[run %s] %s', self::$runPrefix, $message);
  }

  /**
   * @param array<string, mixed> $config
   */
  protected static function createConfigurationProvider(array $config): ConfigurationProviderInterface {
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

  private static function ensureBackendReachable(): void {
    if (static::probeBackend()) {
      return;
    }

    StorageTestEnvironment::abortBackendNotReachable(static::backendName());
  }

  private static function prepareDirectories(AbstractStorage $storage): void {
    if (!$storage instanceof DirectoryStorageInterface) {
      return;
    }

    foreach (['slink', self::IMAGE_DIRECTORY, self::CACHE_DIRECTORY] as $directory) {
      if (!$storage->exists($directory)) {
        $storage->mkdir($directory);
      }
    }
  }

  protected function deterministicBytes(int $length): string {
    $pattern = '';

    for ($byte = 0; $byte < 256; $byte++) {
      $pattern .= \chr($byte);
    }

    return substr(str_repeat($pattern, intdiv($length, 256) + 1), 0, $length);
  }

  private static function fixturePath(): string {
    return \dirname(__DIR__, 2) . '/fixtures/test.jpg';
  }

  private static function fixtureBytes(): string {
    $bytes = file_get_contents(self::fixturePath());

    if ($bytes === false) {
      self::fail('Unable to read the binary fixture at ' . self::fixturePath());
    }

    return $bytes;
  }
}
