<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Domain\ValueObject;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Share\Domain\Enum\ShareType;
use Slink\Share\Domain\ValueObject\ShareResult;
use Slink\Share\Domain\ValueObject\TargetPath;

final class ShareResultTest extends TestCase {
  #[Test]
  public function itCreatesShortUrlResult(): void {
    $shortCode = 'aBcD1234';

    $result = ShareResult::shortUrl($shortCode);

    $this->assertEquals(ShareType::ShortUrl, $result->getType());
    $this->assertEquals($shortCode, $result->getShortCode());
    $this->assertNull($result->getTargetPath());
  }

  #[Test]
  public function itCreatesSignedResult(): void {
    $targetPath = TargetPath::fromString('/image/test.jpg?s=signature');

    $result = ShareResult::signed($targetPath);

    $this->assertEquals(ShareType::Signed, $result->getType());
    $this->assertSame($targetPath, $result->getTargetPath());
    $this->assertNull($result->getShortCode());
  }

  #[Test]
  public function itGeneratesShortUrlPath(): void {
    $shortCode = 'xYz98765';

    $result = ShareResult::shortUrl($shortCode);

    $this->assertEquals("i/{$shortCode}", $result->getUrl());
  }

  #[Test]
  public function itReturnsTargetUrlForSigned(): void {
    $targetPath = TargetPath::fromString('/image/test.jpg?width=800&height=600&s=signature');

    $result = ShareResult::signed($targetPath);

    $this->assertEquals($targetPath->toString(), $result->getUrl());
  }

  #[Test]
  public function itSerializesShortUrlToPayload(): void {
    $shortCode = 'aBcD1234';

    $result = ShareResult::shortUrl($shortCode);
    $payload = $result->toPayload();

    $this->assertArrayHasKey('type', $payload);
    $this->assertArrayHasKey('shortCode', $payload);
    $this->assertEquals('shortUrl', $payload['type']);
    $this->assertEquals($shortCode, $payload['shortCode']);
    $this->assertArrayNotHasKey('targetUrl', $payload);
  }

  #[Test]
  public function itSerializesSignedToPayload(): void {
    $targetPath = TargetPath::fromString('/image/test.jpg');

    $result = ShareResult::signed($targetPath);
    $payload = $result->toPayload();

    $this->assertArrayHasKey('type', $payload);
    $this->assertArrayHasKey('targetUrl', $payload);
    $this->assertEquals('signed', $payload['type']);
    $this->assertEquals($targetPath->toString(), $payload['targetUrl']);
    $this->assertArrayNotHasKey('shortCode', $payload);
  }
}
