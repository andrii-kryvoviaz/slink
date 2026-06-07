<?php

declare(strict_types=1);

namespace Unit\Slink\Image\Infrastructure\ChunkedUpload;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Infrastructure\ChunkedUpload\Exception\ExpiredUploadTokenException;
use Slink\Image\Infrastructure\ChunkedUpload\Exception\InvalidUploadTokenException;
use Slink\Image\Infrastructure\ChunkedUpload\UploadToken;
use Slink\Image\Infrastructure\ChunkedUpload\UploadTokenCodec;

final class UploadTokenCodecTest extends TestCase {
  private const string SECRET = 'test-secret-key';

  private function codec(): UploadTokenCodec {
    return new UploadTokenCodec(self::SECRET);
  }

  private function token(int $expiresAt = 9999999999): UploadToken {
    return UploadToken::create(
      uploadId: 'upload-1',
      ownerId: 'owner-1',
      isGuest: false,
      fileName: 'sample.png',
      totalSize: 2048,
      mimeType: 'image/png',
      isPublic: true,
      description: 'a description',
      tagIds: ['tag-1'],
      collectionIds: ['collection-1'],
      totalChunks: 2,
      expiresAt: $expiresAt,
    );
  }

  #[Test]
  public function itRoundTripsAValidToken(): void {
    $codec = $this->codec();
    $token = $this->token();

    $decoded = $codec->decode($codec->encode($token), 1000);

    self::assertEquals($token, $decoded);
  }

  #[Test]
  public function itRejectsATamperedPayload(): void {
    $codec = $this->codec();
    $encoded = $codec->encode($this->token());

    [$payload, $signature] = \explode('.', $encoded);
    $tamperedPayload = $payload . 'x';

    $this->expectException(InvalidUploadTokenException::class);

    $codec->decode($tamperedPayload . '.' . $signature, 1000);
  }

  #[Test]
  public function itRejectsATokenSignedWithAnotherSecret(): void {
    $foreign = new UploadTokenCodec('other-secret');
    $encoded = $foreign->encode($this->token());

    $this->expectException(InvalidUploadTokenException::class);

    $this->codec()->decode($encoded, 1000);
  }

  #[Test]
  public function itRejectsAMalformedToken(): void {
    $this->expectException(InvalidUploadTokenException::class);

    $this->codec()->decode('not-a-valid-token', 1000);
  }

  #[Test]
  public function itRejectsAnExpiredToken(): void {
    $codec = $this->codec();
    $encoded = $codec->encode($this->token(expiresAt: 500));

    $this->expectException(ExpiredUploadTokenException::class);

    $codec->decode($encoded, 1000);
  }
}
