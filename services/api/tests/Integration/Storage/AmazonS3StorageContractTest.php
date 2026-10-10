<?php

declare(strict_types=1);

namespace Tests\Integration\Storage;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Shared\Domain\ValueObject\BaseFileName;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;
use Slink\Shared\Infrastructure\Encryption\EncryptionService;
use Slink\Shared\Infrastructure\FileSystem\Storage\AbstractStorage;
use Slink\Shared\Infrastructure\FileSystem\Storage\AmazonS3Storage;

#[Group('storage-integration')]
final class AmazonS3StorageContractTest extends StorageContractTestCase {
  private const int BULK_OBJECT_COUNT = 1001;

  #[\Override]
  protected static function backendName(): string {
    return 'S3';
  }

  #[\Override]
  protected static function probeBackend(): bool {
    return StorageTestEnvironment::probeHttp(StorageTestEnvironment::s3Endpoint() . '/healthz');
  }

  #[\Override]
  protected static function createStorage(): AbstractStorage {
    EncryptionRegistry::setService(new EncryptionService('storage-integration-test-secret'));

    return new AmazonS3Storage(self::createConfigurationProvider([
      'storage.adapter.s3.region' => 'us-east-1',
      'storage.adapter.s3.bucket' => StorageTestEnvironment::s3Bucket(),
      'storage.adapter.s3.key' => StorageTestEnvironment::s3Key(),
      'storage.adapter.s3.secret' => StorageTestEnvironment::s3Secret(),
      'storage.adapter.s3.endpoint' => StorageTestEnvironment::s3Endpoint(),
      'storage.adapter.s3.useCustomProvider' => true,
      'storage.adapter.s3.forcePathStyle' => true,
      'storage.adapter.s3.useIamRole' => false,
    ]));
  }

  #[Test]
  public function itClearsCacheBeyondSingleListingPage(): void {
    $this->storage()->clearCache();
    $this->writeBulkCacheEntries($this->uniqueFileName('bulk-clear', 'jpg'));

    self::assertSame(
      self::BULK_OBJECT_COUNT,
      $this->storage()->clearCache(),
      $this->describe('clearCache must delete and count every cache entry across all listing pages'),
    );
    self::assertSame(
      0,
      $this->storage()->clearCache(),
      $this->describe('no cache entry may remain after clearCache'),
    );
  }

  #[Test]
  public function itDeletesImageCacheVariantsBeyondSingleListingPage(): void {
    $this->storage()->clearCache();
    $fileName = $this->uniqueFileName('bulk-variants', 'jpg');
    $this->uploadFixture($fileName);
    $this->writeBulkCacheEntries($fileName);

    $this->storage()->delete($fileName);

    self::assertFalse(
      $this->storage()->exists($this->imagePath($fileName)),
      $this->describe('delete must remove the original ' . $fileName . ' alongside more than one listing page of variants'),
    );
    self::assertSame(
      0,
      $this->storage()->clearCache(),
      $this->describe('delete must remove every cache variant of ' . $fileName . ' across all listing pages'),
    );
  }

  private function writeBulkCacheEntries(string $fileName): void {
    $stem = BaseFileName::fromFileName($fileName)->toString();
    $this->writeCacheEntries(array_map(static fn(int $index): string => $stem . '-w' . $index . '.jpg', range(1, self::BULK_OBJECT_COUNT)));
  }
}
