<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Shared\Infrastructure\FileSystem\Storage;

use Aws\CommandInterface;
use Aws\MockHandler;
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
use Slink\Shared\Infrastructure\Exception\Storage\AmazonS3Exception;
use Slink\Shared\Infrastructure\FileSystem\Storage\AmazonS3Storage;

final class AmazonS3StorageTest extends TestCase {
  private const int MOCK_HANDLER_CAPACITY = 8;

  /** @var list<CommandInterface> */
  private array $commands = [];

  /** @var list<list<string>> */
  private array $listedPages = [];

  /** @var Result<int|string, mixed> */
  private Result $deleteResult;

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
    $storage = $this->createRecordingStorage([[]]);

    $storage->delete('img.2024-06-24.avif');

    $this->assertSame('cache/img.2024-06-24-', $this->listingRequests()[0]['Prefix'] ?? null);
  }

  #[Test]
  public function itDerivesDeletePrefixForExtensionlessFileName(): void {
    $storage = $this->createRecordingStorage([[]]);

    set_error_handler(function(int $errno, string $errstr): bool {
      throw new \ErrorException($errstr, 0, $errno);
    }, E_WARNING | E_DEPRECATED);

    try {
      $storage->delete('nodotname');
    } finally {
      restore_error_handler();
    }

    $this->assertSame('cache/nodotname-', $this->listingRequests()[0]['Prefix'] ?? null);
    $this->assertSame([['nodotname']], $this->deletedBatches());
  }

  #[Test]
  public function itDeletesOriginalAlongsideCacheVariantsAcrossAllListedPages(): void {
    $storage = $this->createRecordingStorage([['cache/img-w200.jpg', 'cache/img-w300-h300-crop.jpg'], ['cache/img-w200.webp']]);

    $storage->delete('img.jpg');

    $this->assertSame([
      ['Bucket' => 'my-bucket', 'Prefix' => 'cache/img-'],
      ['Bucket' => 'my-bucket', 'Prefix' => 'cache/img-', 'ContinuationToken' => '1'],
    ], $this->listingRequests());
    $this->assertSame([['img.jpg', 'cache/img-w200.jpg', 'cache/img-w300-h300-crop.jpg', 'cache/img-w200.webp']], $this->deletedBatches());
  }

  #[Test]
  public function itDeletesOnlyOriginalWhenImageHasNoCacheVariants(): void {
    $storage = $this->createRecordingStorage([[]]);

    $storage->delete('img.jpg');

    $this->assertSame(['ListObjectsV2', 'DeleteObjects'], $this->commandNames());
    $this->assertSame([['img.jpg']], $this->deletedBatches());
  }

  #[Test]
  public function itSplitsDeleteBatchesAtTheThousandKeyLimit(): void {
    $variants = array_map(static fn(int $index): string => sprintf('cache/img-w%d.jpg', $index), range(1, 1000));
    $storage = $this->createRecordingStorage([$variants]);

    $storage->delete('img.jpg');

    $this->assertSame([1000, 1], array_map(count(...), $this->deletedBatches()));
    $this->assertSame(['img.jpg', ...$variants], array_merge(...$this->deletedBatches()));
  }

  #[Test]
  public function itThrowsWhenDeleteObjectsReportsPerKeyErrors(): void {
    $storage = $this->createRecordingStorage(
      [['cache/img-w200.jpg']],
      new Result(['Errors' => [['Key' => 'cache/img-w200.jpg', 'Code' => 'AccessDenied', 'Message' => 'Access Denied']]]),
    );

    $this->expectException(AmazonS3Exception::class);
    $this->expectExceptionMessage('AccessDenied');

    $storage->delete('img.jpg');
  }

  #[Test]
  public function itClearsCacheAcrossAllListedPagesAndReturnsSummedCount(): void {
    $storage = $this->createRecordingStorage([['cache/a.jpg', 'cache/b.jpg'], ['cache/c.jpg']]);

    $count = $storage->clearCache();

    $this->assertSame(3, $count);
    $this->assertSame([
      ['Bucket' => 'my-bucket', 'Prefix' => 'cache/'],
      ['Bucket' => 'my-bucket', 'Prefix' => 'cache/', 'ContinuationToken' => '1'],
    ], $this->listingRequests());
    $this->assertSame([['cache/a.jpg', 'cache/b.jpg', 'cache/c.jpg']], $this->deletedBatches());
  }

  #[Test]
  public function itSkipsDeletionWhenCacheListingIsEmpty(): void {
    $storage = $this->createRecordingStorage([[]]);

    $count = $storage->clearCache();

    $this->assertSame(0, $count);
    $this->assertSame(['ListObjectsV2'], $this->commandNames());
  }

  /**
   * @param list<list<string>> $listedPages
   * @param Result<int|string, mixed> $deleteResult
   */
  private function createRecordingStorage(array $listedPages, Result $deleteResult = new Result([])): AmazonS3Storage {
    $this->listedPages = $listedPages;
    $this->deleteResult = $deleteResult;

    return $this->createStorageWithHandler(new MockHandler(array_fill(0, self::MOCK_HANDLER_CAPACITY, $this->respondTo(...))));
  }

  /**
   * @return Result<int|string, mixed>
   */
  private function listingResult(int $page): Result {
    $lastPage = count($this->listedPages) - 1;
    $listing = ['IsTruncated' => $page < $lastPage];

    if ($this->listedPages[$page] !== []) {
      $listing['Contents'] = array_map(static fn(string $key): array => ['Key' => $key], $this->listedPages[$page]);
    }

    if ($page < $lastPage) {
      $listing['NextContinuationToken'] = (string) ($page + 1);
    }

    return new Result($listing);
  }

  /**
   * @return Result<int|string, mixed>
   */
  private function respondTo(CommandInterface $command): Result {
    $this->commands[] = $command;

    if ($command->getName() === 'ListObjectsV2') {
      return $this->listingResult((int) ($command['ContinuationToken'] ?? 0));
    }

    return $this->deleteResult;
  }

  /**
   * @return list<string>
   */
  private function commandNames(): array {
    return array_map(static fn(CommandInterface $command): string => $command->getName(), $this->commands);
  }

  /**
   * @return list<array<string, mixed>>
   */
  private function listingRequests(): array {
    $listingKeys = array_flip(['Bucket', 'Prefix', 'ContinuationToken']);

    return array_map(static fn(CommandInterface $command): array => array_intersect_key($command->toArray(), $listingKeys), $this->commandsNamed('ListObjectsV2'));
  }

  /**
   * @return list<list<string>>
   */
  private function deletedBatches(): array {
    return array_map(static fn(CommandInterface $command): array => array_column($command['Delete']['Objects'], 'Key'), $this->commandsNamed('DeleteObjects'));
  }

  /**
   * @return list<CommandInterface>
   */
  private function commandsNamed(string $name): array {
    return array_values(array_filter($this->commands, static fn(CommandInterface $command): bool => $command->getName() === $name));
  }

  private function createStorageWithHandler(MockHandler $handler): AmazonS3Storage {
    $client = new S3Client([
      'region' => 'us-east-1',
      'version' => 'latest',
      'credentials' => ['key' => 'access-key', 'secret' => 'secret-key'],
      'handler' => $handler,
    ]);

    $settings = $this->createStub(AmazonS3StorageSettings::class);
    $settings->method('getBucket')->willReturn('my-bucket');

    $reflection = new \ReflectionClass(AmazonS3Storage::class);
    $storage = $reflection->newInstanceWithoutConstructor();

    $reflection->getProperty('client')->setValue($storage, $client);
    $reflection->getProperty('settings')->setValue($storage, $settings);

    return $storage;
  }

  private function createStorageWithObjectBytes(string $bytes): AmazonS3Storage {
    return $this->createStorageWithHandler(new MockHandler([new Result(['Body' => Utils::streamFor($bytes)])]));
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
