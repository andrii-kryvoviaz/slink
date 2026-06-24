<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Shared\Infrastructure\FileSystem\Storage;

use Aws\Result;
use Aws\S3\S3Client;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Exception\S3BucketNotConfiguredException;
use Slink\Settings\Domain\Exception\S3CredentialsNotConfiguredException;
use Slink\Settings\Domain\Exception\S3RegionNotConfiguredException;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Settings\Domain\ValueObject\Storage\AmazonS3StorageSettings;
use Slink\Shared\Infrastructure\FileSystem\Storage\AmazonS3Storage;

final class AmazonS3StorageTest extends TestCase {
  #[Test]
  public function itThrowsExceptionWhenRegionIsMissingForAws(): void {
    $configProvider = $this->createConfigProvider([
      'storage.adapter.s3.region' => '',
      'storage.adapter.s3.bucket' => 'my-bucket',
      'storage.adapter.s3.key' => 'access-key',
      'storage.adapter.s3.secret' => 'secret-key',
      'storage.adapter.s3.endpoint' => null,
      'storage.adapter.s3.useCustomProvider' => false,
      'storage.adapter.s3.forcePathStyle' => false,
    ]);

    $this->expectException(S3RegionNotConfiguredException::class);

    AmazonS3StorageSettings::fromConfig($configProvider);
  }

  #[Test]
  public function itAllowsEmptyRegionForCustomProvider(): void {
    $configProvider = $this->createConfigProvider([
      'storage.adapter.s3.region' => '',
      'storage.adapter.s3.bucket' => 'my-bucket',
      'storage.adapter.s3.key' => 'access-key',
      'storage.adapter.s3.secret' => 'secret-key',
      'storage.adapter.s3.endpoint' => 'http://minio:9000',
      'storage.adapter.s3.useCustomProvider' => true,
      'storage.adapter.s3.forcePathStyle' => true,
    ]);

    $settings = AmazonS3StorageSettings::fromConfig($configProvider);

    $this->assertSame('my-bucket', $settings->getBucket());
  }

  #[Test]
  public function itThrowsExceptionWhenBucketIsMissing(): void {
    $configProvider = $this->createConfigProvider([
      'storage.adapter.s3.region' => 'us-east-1',
      'storage.adapter.s3.bucket' => '',
      'storage.adapter.s3.key' => 'access-key',
      'storage.adapter.s3.secret' => 'secret-key',
      'storage.adapter.s3.endpoint' => null,
      'storage.adapter.s3.useCustomProvider' => false,
      'storage.adapter.s3.forcePathStyle' => false,
    ]);

    $this->expectException(S3BucketNotConfiguredException::class);

    AmazonS3StorageSettings::fromConfig($configProvider);
  }

  #[Test]
  public function itThrowsExceptionWhenAccessKeyIsMissing(): void {
    $configProvider = $this->createConfigProvider([
      'storage.adapter.s3.region' => 'us-east-1',
      'storage.adapter.s3.bucket' => 'my-bucket',
      'storage.adapter.s3.key' => '',
      'storage.adapter.s3.secret' => 'secret-key',
      'storage.adapter.s3.endpoint' => null,
      'storage.adapter.s3.useCustomProvider' => false,
      'storage.adapter.s3.forcePathStyle' => false,
    ]);

    $this->expectException(S3CredentialsNotConfiguredException::class);

    AmazonS3StorageSettings::fromConfig($configProvider);
  }

  #[Test]
  public function itThrowsExceptionWhenSecretKeyIsMissing(): void {
    $configProvider = $this->createConfigProvider([
      'storage.adapter.s3.region' => 'us-east-1',
      'storage.adapter.s3.bucket' => 'my-bucket',
      'storage.adapter.s3.key' => 'access-key',
      'storage.adapter.s3.secret' => '',
      'storage.adapter.s3.endpoint' => null,
      'storage.adapter.s3.useCustomProvider' => false,
      'storage.adapter.s3.forcePathStyle' => false,
    ]);

    $this->expectException(S3CredentialsNotConfiguredException::class);

    AmazonS3StorageSettings::fromConfig($configProvider);
  }

  #[Test]
  public function itInitializesWithValidAwsConfig(): void {
    $configProvider = $this->createConfigProvider([
      'storage.adapter.s3.region' => 'us-east-1',
      'storage.adapter.s3.bucket' => 'my-bucket',
      'storage.adapter.s3.key' => 'access-key',
      'storage.adapter.s3.secret' => 'secret-key',
      'storage.adapter.s3.endpoint' => null,
      'storage.adapter.s3.useCustomProvider' => false,
      'storage.adapter.s3.forcePathStyle' => false,
    ]);

    $settings = AmazonS3StorageSettings::fromConfig($configProvider);

    $this->assertSame('us-east-1', $settings->getRegion());
    $this->assertSame('my-bucket', $settings->getBucket());
  }

  #[Test]
  public function itInitializesWithValidCustomProviderConfig(): void {
    $configProvider = $this->createConfigProvider([
      'storage.adapter.s3.region' => 'auto',
      'storage.adapter.s3.bucket' => 'my-bucket',
      'storage.adapter.s3.key' => 'access-key',
      'storage.adapter.s3.secret' => 'secret-key',
      'storage.adapter.s3.endpoint' => 'http://minio:9000',
      'storage.adapter.s3.useCustomProvider' => true,
      'storage.adapter.s3.forcePathStyle' => true,
    ]);

    $settings = AmazonS3StorageSettings::fromConfig($configProvider);

    $this->assertSame('auto', $settings->getRegion());
    $this->assertTrue($settings->usesCustomProvider());
  }

  #[Test]
  public function itAutoDetectsCustomProviderWhenEndpointIsSet(): void {
    $configProvider = $this->createConfigProvider([
      'storage.adapter.s3.region' => '',
      'storage.adapter.s3.bucket' => 'my-bucket',
      'storage.adapter.s3.key' => 'access-key',
      'storage.adapter.s3.secret' => 'secret-key',
      'storage.adapter.s3.endpoint' => 'http://minio:9000',
      'storage.adapter.s3.useCustomProvider' => null,
      'storage.adapter.s3.forcePathStyle' => false,
    ]);

    $settings = AmazonS3StorageSettings::fromConfig($configProvider);

    $this->assertTrue($settings->usesCustomProvider());
  }

  #[Test]
  public function itStreamsObjectBodyAsResourceWithoutTemporaryFile(): void {
    $fileName = 'object-image.jpg';
    $bytes = 'remote s3 content';

    $storage = $this->createStorageWithObjectBytes($bytes);

    $temporaryBefore = $this->countTemporaryCopies();

    $stream = $storage->readStream($fileName);
    $resource = $stream->resource();
    $this->assertSame($bytes, stream_get_contents($resource));
    $this->assertSame($temporaryBefore, $this->countTemporaryCopies());
  }

  #[Test]
  public function itClosesResourceWhenStreamIsDestroyed(): void {
    $fileName = 'object-image.jpg';

    $storage = $this->createStorageWithObjectBytes('remote s3 content');

    $stream = $storage->readStream($fileName);
    $resource = $stream->resource();

    unset($stream);

    $this->assertFalse(is_resource($resource));
  }

  #[Test]
  public function itResolvesCachePathUnderCacheDirectory(): void {
    $storage = $this->createStorageWithObjectBytes('');

    $this->assertSame('cache/abc123-w350.jpg', $storage->cachePath('abc123-w350.jpg'));
  }

  #[Test]
  public function itReadsSourceAsStreamForObjectStorage(): void {
    $bytes = 'remote s3 content';

    $storage = $this->createStorageWithObjectBytes($bytes);

    $source = $storage->readSource('object-image.jpg');

    $this->assertFalse($source->hasLocalPath());
    $this->assertSame($bytes, stream_get_contents($source->getStream()->resource()));
  }

  #[Test]
  public function itDerivesDeletePrefixFromFullStemForMultiDotNames(): void {
    $capturedPrefix = null;
    $client = $this->createPrefixCapturingClient($capturedPrefix);
    $storage = $this->createStorageWithClient($client);

    $storage->delete('img.2024-06-24.avif');

    $this->assertSame('img.2024-06-24', $capturedPrefix);
  }

  #[Test]
  public function itDerivesDeletePrefixForExtensionlessFileName(): void {
    $capturedPrefix = null;
    $client = $this->createPrefixCapturingClient($capturedPrefix);
    $storage = $this->createStorageWithClient($client);

    set_error_handler(function(int $errno, string $errstr): bool {
      throw new \ErrorException($errstr, 0, $errno);
    }, E_WARNING | E_DEPRECATED);

    try {
      $storage->delete('nodotname');
    } finally {
      restore_error_handler();
    }

    $this->assertSame('nodotname', $capturedPrefix);
  }

  private function createPrefixCapturingClient(?string &$capturedPrefix): S3Client {
    return new class($capturedPrefix) extends S3Client {
      /** @var \Closure(array<string, mixed>): void */
      private \Closure $capture;

      public function __construct(?string &$capturedPrefix) {
        $this->capture = static function(array $args) use (&$capturedPrefix): void {
          $capturedPrefix = $args['Prefix'] ?? null;
        };
      }

      /**
       * @param array<string, mixed> $args
       * @return Result<int|string, mixed>
       */
      public function listObjectsV2(array $args = []): Result {
        ($this->capture)($args);

        return new Result(['Contents' => []]);
      }
    };
  }

  private function createStorageWithClient(S3Client $client): AmazonS3Storage {
    $settings = $this->createStub(AmazonS3StorageSettings::class);
    $settings->method('getBucket')->willReturn('my-bucket');

    $reflection = new \ReflectionClass(AmazonS3Storage::class);
    $storage = $reflection->newInstanceWithoutConstructor();

    $reflection->getProperty('client')->setValue($storage, $client);
    $reflection->getProperty('settings')->setValue($storage, $settings);

    return $storage;
  }

  private function createStorageWithObjectBytes(string $bytes): AmazonS3Storage {
    $client = new class($bytes) extends S3Client {
      public function __construct(private readonly string $bytes) {
      }

      /**
       * @param array<string, mixed> $args
       * @return Result<int|string, mixed>
       */
      public function getObject(array $args = []): Result {
        return new Result(['Body' => Utils::streamFor($this->bytes)]);
      }
    };

    $settings = $this->createStub(AmazonS3StorageSettings::class);
    $settings->method('getBucket')->willReturn('my-bucket');

    $reflection = new \ReflectionClass(AmazonS3Storage::class);
    $storage = $reflection->newInstanceWithoutConstructor();

    $reflection->getProperty('client')->setValue($storage, $client);
    $reflection->getProperty('settings')->setValue($storage, $settings);

    return $storage;
  }

  private function countTemporaryCopies(): int {
    $files = glob(sys_get_temp_dir() . '/slink_src_*');

    return $files === false ? 0 : count($files);
  }

  /**
   * @param array<string, mixed> $config
   */
  private function createConfigProvider(array $config): ConfigurationProviderInterface {
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->willReturnCallback(fn(string $key) => $config[$key] ?? null);

    return $configProvider;
  }
}
