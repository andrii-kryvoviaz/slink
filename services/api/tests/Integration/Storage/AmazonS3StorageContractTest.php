<?php

declare(strict_types=1);

namespace Tests\Integration\Storage;

use PHPUnit\Framework\Attributes\Group;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;
use Slink\Shared\Infrastructure\Encryption\EncryptionService;
use Slink\Shared\Infrastructure\FileSystem\Storage\AbstractStorage;
use Slink\Shared\Infrastructure\FileSystem\Storage\AmazonS3Storage;

#[Group('storage-integration')]
final class AmazonS3StorageContractTest extends StorageContractTestCase {
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
}
