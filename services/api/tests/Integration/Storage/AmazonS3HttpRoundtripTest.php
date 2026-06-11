<?php

declare(strict_types=1);

namespace Tests\Integration\Storage;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

#[Group('storage-integration')]
final class AmazonS3HttpRoundtripTest extends HttpTestCase {
  /**
   * @var array<string, string|null>
   */
  private static array $previousEnv = [];

  public static function setUpBeforeClass(): void {
    self::ensureSeaweedFsReachable();
    self::applyStorageEnv();

    parent::setUpBeforeClass();
  }

  public static function tearDownAfterClass(): void {
    self::restoreStorageEnv();

    parent::tearDownAfterClass();
  }

  #[Test]
  public function itUploadsReadsAndDeletesImageThroughHttpWithS3Storage(): void {
    $this->createUser('s3-owner@local.test', 's3owner', self::PASSWORD);
    $token = $this->login('s3owner', self::PASSWORD);

    $imageId = $this->uploadImage($token, true);
    self::assertNotSame('', $imageId);

    $fileName = $this->fetchFileName($token, $imageId);

    $status = $this->apiRequest('GET', '/api/image/' . $fileName, $token);
    self::assertSame(200, $status, 'Image content request failed: ' . (string) $this->client->getResponse()->getContent());

    $response = $this->client->getResponse();
    self::assertNotSame('', (string) $response->getContent(), 'Image content served from S3 must not be empty.');
    self::assertStringStartsWith('image/', (string) $response->headers->get('Content-Type'));

    $deleteStatus = $this->apiRequest('DELETE', '/api/image/' . $imageId, $token);
    self::assertContains($deleteStatus, [200, 204], 'Delete failed: ' . (string) $this->client->getResponse()->getContent());

    self::assertSame(404, $this->apiRequest('GET', '/api/image/' . $fileName, $token));
  }

  private function fetchFileName(string $token, string $imageId): string {
    $status = $this->apiRequest('GET', \sprintf('/api/image/%s/detail', $imageId), $token);
    self::assertSame(200, $status, 'Detail request failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{data?: array{fileName?: string}} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $fileName = $payload['data']['fileName'] ?? '';

    self::assertNotSame('', $fileName, 'Image detail must expose the stored file name.');

    return $fileName;
  }

  private static function ensureSeaweedFsReachable(): void {
    if (StorageTestEnvironment::probeHttp(StorageTestEnvironment::s3Endpoint() . '/healthz')) {
      return;
    }

    StorageTestEnvironment::abortBackendNotReachable('S3');
  }

  private static function applyStorageEnv(): void {
    $overrides = [
      'STORAGE_PROVIDER' => 's3',
      'AMAZON_S3_REGION' => 'us-east-1',
      'AMAZON_S3_BUCKET' => StorageTestEnvironment::s3Bucket(),
      'AMAZON_S3_ACCESS_KEY_ID' => StorageTestEnvironment::s3Key(),
      'AMAZON_S3_SECRET_ACCESS_KEY' => StorageTestEnvironment::s3Secret(),
      'AMAZON_S3_ENDPOINT' => StorageTestEnvironment::s3Endpoint(),
      'AMAZON_S3_USE_CUSTOM_PROVIDER' => 'true',
      'AMAZON_S3_FORCE_PATH_STYLE' => 'true',
      'AMAZON_S3_USE_IAM_ROLE' => 'false',
    ];

    foreach ($overrides as $name => $value) {
      self::$previousEnv[$name] = isset($_ENV[$name]) && \is_string($_ENV[$name]) ? $_ENV[$name] : null;

      $_ENV[$name] = $value;
      $_SERVER[$name] = $value;
      \putenv($name . '=' . $value);
    }
  }

  private static function restoreStorageEnv(): void {
    foreach (self::$previousEnv as $name => $value) {
      if ($value === null) {
        unset($_ENV[$name], $_SERVER[$name]);
        \putenv($name);

        continue;
      }

      $_ENV[$name] = $value;
      $_SERVER[$name] = $value;
      \putenv($name . '=' . $value);
    }

    self::$previousEnv = [];
  }
}
