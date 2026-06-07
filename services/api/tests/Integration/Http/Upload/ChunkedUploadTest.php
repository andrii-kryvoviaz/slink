<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Upload;

use PHPUnit\Framework\Attributes\Test;
use Slink\Image\Infrastructure\ChunkedUpload\UploadToken;
use Slink\Image\Infrastructure\ChunkedUpload\UploadTokenCodec;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Tests\Integration\Http\HttpTestCase;

final class ChunkedUploadTest extends HttpTestCase {
  private string $authToken = '';

  private function bootUser(): void {
    $this->createUser('chunked@local.test', 'chunkeduser', self::PASSWORD);
    $this->authToken = $this->login('chunkeduser', self::PASSWORD);
  }

  private function chunkSize(): int {
    /** @var ConfigurationProviderInterface<object> $provider */
    $provider = static::getContainer()->get(ConfigurationProviderInterface::class);

    return \max(1, convertSizeToBytes((string) $provider->get('image.chunkSize')));
  }

  /**
   * @return array<int, string>
   */
  private function splitIntoChunks(string $bytes, int $chunkSize): array {
    $chunks = \str_split($bytes, \max(1, $chunkSize));

    return $chunks === [] ? [''] : $chunks;
  }

  private function pngBytes(int $padding = 0): string {
    $width = 2;
    $height = 2;

    $ihdr = \pack('N', $width)
      . \pack('N', $height)
      . \chr(8)
      . \chr(2)
      . \chr(0)
      . \chr(0)
      . \chr(0);

    $raw = '';
    for ($y = 0; $y < $height; $y++) {
      $raw .= \chr(0);
      for ($x = 0; $x < $width * 3; $x++) {
        $raw .= \chr(\random_int(0, 255));
      }
    }

    $compressed = \gzcompress($raw, 9);
    if ($compressed === false) {
      throw new \RuntimeException('Unable to compress test image data.');
    }

    $textChunk = $padding > 0
      ? $this->pngChunk('tEXt', "Comment\x00" . \str_repeat('A', $padding))
      : '';

    return "\x89PNG\r\n\x1a\n"
      . $this->pngChunk('IHDR', $ihdr)
      . $this->pngChunk('IDAT', $compressed)
      . $textChunk
      . $this->pngChunk('IEND', '');
  }

  private function multiChunkPng(int $chunkSize): string {
    return $this->pngBytes($chunkSize + 256);
  }

  private function pngChunk(string $type, string $data): string {
    return \pack('N', \strlen($data))
      . $type
      . $data
      . \pack('N', \crc32($type . $data));
  }

  /**
   * @param array<string, mixed> $payload
   * @return array{0: int, 1: array<string, mixed>}
   */
  private function init(array $payload): array {
    $this->client->request(
      'POST',
      '/api/upload/chunked',
      [],
      [],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->authToken, 'CONTENT_TYPE' => 'application/json'],
      \json_encode($payload, JSON_THROW_ON_ERROR),
    );

    $response = $this->client->getResponse();

    /** @var array{data?: array<string, mixed>} $body */
    $body = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR) ?: [];

    return [$response->getStatusCode(), $body['data'] ?? []];
  }

  private function putChunk(string $uploadId, int $index, string $bytes, ?string $uploadToken, bool $complete = false): int {
    return $this->putChunkResponse($uploadId, $index, $bytes, $uploadToken, $complete)[0];
  }

  /**
   * @return array{0: int, 1: array<string, mixed>}
   */
  private function putChunkResponse(string $uploadId, int $index, string $bytes, ?string $uploadToken, bool $complete = false): array {
    $headers = [
      'CONTENT_TYPE' => 'application/octet-stream',
    ];

    if ($this->authToken !== '') {
      $headers['HTTP_AUTHORIZATION'] = 'Bearer ' . $this->authToken;
    }

    if ($uploadToken !== null) {
      $headers['HTTP_X_UPLOAD_TOKEN'] = $uploadToken;
    }

    if ($complete) {
      $headers['HTTP_X_UPLOAD_COMPLETE'] = 'true';
    }

    $this->client->request(
      'PUT',
      \sprintf('/api/upload/chunked/%s/%d', $uploadId, $index),
      [],
      [],
      $headers,
      $bytes,
    );

    $response = $this->client->getResponse();

    /** @var array{data?: array<string, mixed>} $body */
    $body = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR) ?: [];

    return [$response->getStatusCode(), $body['data'] ?? []];
  }

  private function statusResponse(string $uploadId, string $uploadToken): \Symfony\Component\HttpFoundation\Response {
    $this->client->request(
      'GET',
      \sprintf('/api/upload/chunked/%s', $uploadId),
      [],
      [],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->authToken, 'HTTP_X_UPLOAD_TOKEN' => $uploadToken],
    );

    return $this->client->getResponse();
  }

  private function codec(): UploadTokenCodec {
    /** @var UploadTokenCodec $codec */
    $codec = static::getContainer()->get(UploadTokenCodec::class);

    return $codec;
  }

  #[Test]
  public function itRejectsAnonymousInitWithUnauthorizedWhenGuestUploadsDisabled(): void {
    $this->setAccessSettings(['allowGuestUploads' => false]);

    $this->client->request(
      'POST',
      '/api/upload/chunked',
      [],
      [],
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode([
        'fileName' => 'sample.png',
        'totalSize' => 1024,
        'mimeType' => 'image/png',
      ], JSON_THROW_ON_ERROR),
    );

    self::assertSame(401, $this->client->getResponse()->getStatusCode());
  }

  #[Test]
  public function itReturnsTokenAndChunkSizeOnInit(): void {
    $this->bootUser();

    [$status, $data] = $this->init([
      'fileName' => 'sample.png',
      'totalSize' => 4096,
      'mimeType' => 'image/png',
    ]);

    self::assertSame(201, $status);
    self::assertArrayHasKey('uploadId', $data);
    self::assertArrayHasKey('token', $data);
    self::assertArrayHasKey('chunkSize', $data);
    self::assertIsString($data['token']);
    self::assertGreaterThan(0, $data['chunkSize']);
  }

  #[Test]
  public function itUploadsImageInChunksAndCompletesOnLastChunk(): void {
    $this->bootUser();

    $chunkSize = $this->chunkSize();
    $bytes = $this->multiChunkPng($chunkSize);
    $chunks = $this->splitIntoChunks($bytes, $chunkSize);
    self::assertGreaterThanOrEqual(2, \count($chunks));

    [, $data] = $this->init([
      'fileName' => 'sample.png',
      'totalSize' => \strlen($bytes),
      'mimeType' => 'image/png',
    ]);

    $uploadId = (string) $data['uploadId'];
    $token = (string) $data['token'];

    $lastIndex = \count($chunks) - 1;

    foreach ($chunks as $index => $chunk) {
      if ($index < $lastIndex) {
        [$status, $chunkData] = $this->putChunkResponse($uploadId, $index, $chunk, $token);
        self::assertSame(200, $status);
        self::assertSame($index, $chunkData['index']);
      }
    }

    [$status, $completionData] = $this->putChunkResponse($uploadId, $lastIndex, $chunks[$lastIndex], $token, complete: true);
    self::assertSame(201, $status);
    self::assertArrayHasKey('id', $completionData);

    $imageId = (string) $completionData['id'];
    self::assertNotSame('', $imageId);

    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/image/%s/detail', $imageId), $this->authToken));
  }

  #[Test]
  public function itReturnsSameImageIdOnIdempotentCompletionReplay(): void {
    $this->bootUser();

    $chunkSize = $this->chunkSize();
    $bytes = $this->multiChunkPng($chunkSize);
    $chunks = $this->splitIntoChunks($bytes, $chunkSize);
    $lastIndex = \count($chunks) - 1;

    [, $data] = $this->init([
      'fileName' => 'sample.png',
      'totalSize' => \strlen($bytes),
      'mimeType' => 'image/png',
    ]);

    $uploadId = (string) $data['uploadId'];
    $token = (string) $data['token'];

    foreach ($chunks as $index => $chunk) {
      if ($index < $lastIndex) {
        $this->putChunk($uploadId, $index, $chunk, $token);
      }
    }

    [$firstStatus, $firstData] = $this->putChunkResponse($uploadId, $lastIndex, $chunks[$lastIndex], $token, complete: true);
    self::assertSame(201, $firstStatus);
    $imageId = (string) $firstData['id'];

    [$replayStatus, $replayData] = $this->putChunkResponse($uploadId, $lastIndex, $chunks[$lastIndex], $token, complete: true);
    self::assertSame(200, $replayStatus);
    self::assertSame($imageId, $replayData['id']);

    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/image/%s/detail', $imageId), $this->authToken));
  }

  #[Test]
  public function itReportsCompletedStatusWithImageId(): void {
    $this->bootUser();

    $chunkSize = $this->chunkSize();
    $bytes = $this->multiChunkPng($chunkSize);
    $chunks = $this->splitIntoChunks($bytes, $chunkSize);
    $lastIndex = \count($chunks) - 1;

    [, $data] = $this->init([
      'fileName' => 'sample.png',
      'totalSize' => \strlen($bytes),
      'mimeType' => 'image/png',
    ]);

    $uploadId = (string) $data['uploadId'];
    $token = (string) $data['token'];

    foreach ($chunks as $index => $chunk) {
      if ($index < $lastIndex) {
        $this->putChunk($uploadId, $index, $chunk, $token);
      }
    }

    [, $completionData] = $this->putChunkResponse($uploadId, $lastIndex, $chunks[$lastIndex], $token, complete: true);
    $imageId = (string) $completionData['id'];

    $response = $this->statusResponse($uploadId, $token);
    self::assertSame(200, $response->getStatusCode());

    /** @var array{data: array<string, mixed>} $body */
    $body = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    self::assertTrue($body['data']['complete']);
    self::assertSame($imageId, $body['data']['id']);
  }

  #[Test]
  public function itRejectsChunkWithoutToken(): void {
    $this->bootUser();

    $bytes = $this->pngBytes();

    [, $data] = $this->init([
      'fileName' => 'sample.png',
      'totalSize' => \strlen($bytes),
      'mimeType' => 'image/png',
    ]);

    self::assertSame(401, $this->putChunk((string) $data['uploadId'], 0, $bytes, null));
  }

  #[Test]
  public function itRejectsChunkWithTamperedToken(): void {
    $this->bootUser();

    $bytes = $this->pngBytes();

    [, $data] = $this->init([
      'fileName' => 'sample.png',
      'totalSize' => \strlen($bytes),
      'mimeType' => 'image/png',
    ]);

    self::assertSame(401, $this->putChunk((string) $data['uploadId'], 0, $bytes, (string) $data['token'] . 'tampered'));
  }

  #[Test]
  public function itReturnsGoneForExpiredToken(): void {
    $this->bootUser();

    $expired = $this->codec()->encode(UploadToken::create(
      uploadId: 'expired-upload',
      ownerId: 'owner',
      isGuest: false,
      fileName: 'sample.png',
      totalSize: 1024,
      mimeType: 'image/png',
      isPublic: false,
      description: '',
      tagIds: [],
      collectionIds: [],
      totalChunks: 1,
      expiresAt: \time() - 10,
    ));

    self::assertSame(410, $this->putChunk('expired-upload', 0, 'data', $expired));
  }

  #[Test]
  public function itRejectsOwnerMismatch(): void {
    $this->bootUser();

    $foreign = $this->codec()->encode(UploadToken::create(
      uploadId: 'foreign-upload',
      ownerId: 'someone-else',
      isGuest: false,
      fileName: 'sample.png',
      totalSize: 1024,
      mimeType: 'image/png',
      isPublic: false,
      description: '',
      tagIds: [],
      collectionIds: [],
      totalChunks: 1,
      expiresAt: \time() + UploadTokenCodec::TTL,
    ));

    self::assertSame(403, $this->putChunk('foreign-upload', 0, 'data', $foreign));
  }

  #[Test]
  public function itRejectsAnonymousRequestWithOwnedToken(): void {
    $this->setAccessSettings(['allowGuestUploads' => true]);

    $owned = $this->codec()->encode(UploadToken::create(
      uploadId: 'owned-upload',
      ownerId: 'owner',
      isGuest: false,
      fileName: 'sample.png',
      totalSize: 1024,
      mimeType: 'image/png',
      isPublic: false,
      description: '',
      tagIds: [],
      collectionIds: [],
      totalChunks: 1,
      expiresAt: \time() + UploadTokenCodec::TTL,
    ));

    self::assertSame(403, $this->putChunk('owned-upload', 0, 'data', $owned));
  }

  #[Test]
  public function itReportsStatusForResume(): void {
    $this->bootUser();

    $chunkSize = $this->chunkSize();
    $bytes = $this->multiChunkPng($chunkSize);
    $chunks = $this->splitIntoChunks($bytes, $chunkSize);
    self::assertGreaterThanOrEqual(2, \count($chunks));

    [, $data] = $this->init([
      'fileName' => 'sample.png',
      'totalSize' => \strlen($bytes),
      'mimeType' => 'image/png',
    ]);

    $uploadId = (string) $data['uploadId'];
    $token = (string) $data['token'];

    $this->putChunk($uploadId, 0, $chunks[0], $token);

    $response = $this->statusResponse($uploadId, $token);
    self::assertSame(200, $response->getStatusCode());

    /** @var array{data: array<string, mixed>} $body */
    $body = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    self::assertSame([0], $body['data']['receivedChunks']);
    self::assertFalse($body['data']['complete']);
    self::assertArrayNotHasKey('id', $body['data']);
  }

  #[Test]
  public function itRejectsCompletionWithMissingChunks(): void {
    $this->bootUser();

    $chunkSize = $this->chunkSize();
    $bytes = $this->multiChunkPng($chunkSize);
    $chunks = $this->splitIntoChunks($bytes, $chunkSize);
    self::assertGreaterThanOrEqual(2, \count($chunks));

    [, $data] = $this->init([
      'fileName' => 'sample.png',
      'totalSize' => \strlen($bytes),
      'mimeType' => 'image/png',
    ]);

    $uploadId = (string) $data['uploadId'];
    $token = (string) $data['token'];

    self::assertSame(422, $this->putChunk($uploadId, 0, $chunks[0], $token, complete: true));
  }

  #[Test]
  public function itRejectsOversizedDeclaredTotalSize(): void {
    $this->bootUser();

    [$status] = $this->init([
      'fileName' => 'huge.png',
      'totalSize' => 50 * 1024 * 1024,
      'mimeType' => 'image/png',
    ]);

    self::assertSame(413, $status);
  }

  #[Test]
  public function itRejectsUnsupportedMimeType(): void {
    $this->bootUser();

    [$status] = $this->init([
      'fileName' => 'note.txt',
      'totalSize' => 1024,
      'mimeType' => 'text/plain',
    ]);

    self::assertSame(422, $status);
  }

  #[Test]
  public function itAbortsUpload(): void {
    $this->bootUser();

    $bytes = $this->pngBytes();

    [, $data] = $this->init([
      'fileName' => 'sample.png',
      'totalSize' => \strlen($bytes),
      'mimeType' => 'image/png',
    ]);

    $uploadId = (string) $data['uploadId'];
    $token = (string) $data['token'];

    $this->putChunk($uploadId, 0, \substr($bytes, 0, 4), $token);

    $this->client->request(
      'DELETE',
      \sprintf('/api/upload/chunked/%s', $uploadId),
      [],
      [],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->authToken, 'HTTP_X_UPLOAD_TOKEN' => $token],
    );

    self::assertSame(204, $this->client->getResponse()->getStatusCode());

    $this->client->request(
      'GET',
      \sprintf('/api/upload/chunked/%s', $uploadId),
      [],
      [],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->authToken, 'HTTP_X_UPLOAD_TOKEN' => $token],
    );

    /** @var array{data: array<string, mixed>} $body */
    $body = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    self::assertSame([], $body['data']['receivedChunks']);
  }

  #[Test]
  public function garbageCollectionReapsCompletedPrefixesAfterTtl(): void {
    $this->bootUser();

    $chunkSize = $this->chunkSize();
    $bytes = $this->multiChunkPng($chunkSize);
    $chunks = $this->splitIntoChunks($bytes, $chunkSize);
    $lastIndex = \count($chunks) - 1;

    [, $data] = $this->init([
      'fileName' => 'sample.png',
      'totalSize' => \strlen($bytes),
      'mimeType' => 'image/png',
    ]);

    $uploadId = (string) $data['uploadId'];
    $token = (string) $data['token'];

    foreach ($chunks as $index => $chunk) {
      if ($index < $lastIndex) {
        $this->putChunk($uploadId, $index, $chunk, $token);
      }
    }

    $this->putChunk($uploadId, $lastIndex, $chunks[$lastIndex], $token, complete: true);

    $chunkStorage = $this->chunkStorage();
    self::assertNotNull($chunkStorage->findCompletedImageId($uploadId));

    $directory = $this->chunkDir() . '/slink/chunks/' . $uploadId;
    self::assertDirectoryExists($directory);
    \touch($directory, \time() - UploadTokenCodec::TTL - 100000);

    $exitCode = $this->runGarbageCollect();
    self::assertSame(0, $exitCode);

    self::assertNull($chunkStorage->findCompletedImageId($uploadId));
  }

  private function chunkDir(): string {
    /** @var ConfigurationProviderInterface<object> $provider */
    $provider = static::getContainer()->get(ConfigurationProviderInterface::class);

    return (string) $provider->get('storage.adapter.local.dir');
  }

  private function chunkStorage(): \Slink\Image\Infrastructure\ChunkedUpload\Storage\ChunkStorageInterface {
    /** @var \Slink\Image\Infrastructure\ChunkedUpload\Storage\ChunkStorageInterface $storage */
    $storage = static::getContainer()->get(\Slink\Image\Infrastructure\ChunkedUpload\Storage\ChunkStorageInterface::class);

    return $storage;
  }

  private function runGarbageCollect(): int {
    $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($this->client->getKernel());
    $application->setAutoExit(false);

    return $application->run(
      new \Symfony\Component\Console\Input\ArrayInput(['command' => 'image:chunked-upload:gc']),
      new \Symfony\Component\Console\Output\BufferedOutput(),
    );
  }
}
