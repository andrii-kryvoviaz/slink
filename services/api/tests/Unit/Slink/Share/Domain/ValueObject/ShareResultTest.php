<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Domain\ValueObject;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Share\Domain\Enum\ShareType;
use Slink\Share\Domain\ValueObject\ShareResult;

final class ShareResultTest extends TestCase {
  #[Test]
  public function itCreatesShortUrlResult(): void {
    $shortCode = 'aBcD1234';
    
    $result = ShareResult::shortUrl($shortCode);
    
    $this->assertEquals(ShareType::ShortUrl, $result->getType());
    $this->assertEquals($shortCode, $result->getShortCode());
    $this->assertNull($result->getTargetUrl());
  }

  #[Test]
  public function itCreatesSignedResult(): void {
    $targetUrl = '/image/test.jpg?s=signature';
    
    $result = ShareResult::signed($targetUrl);
    
    $this->assertEquals(ShareType::Signed, $result->getType());
    $this->assertEquals($targetUrl, $result->getTargetUrl());
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
    $targetUrl = '/image/test.jpg?width=800&height=600&s=signature';
    
    $result = ShareResult::signed($targetUrl);
    
    $this->assertEquals($targetUrl, $result->getUrl());
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
    $targetUrl = '/image/test.jpg';
    
    $result = ShareResult::signed($targetUrl);
    $payload = $result->toPayload();
    
    $this->assertArrayHasKey('type', $payload);
    $this->assertArrayHasKey('targetUrl', $payload);
    $this->assertEquals('signed', $payload['type']);
    $this->assertEquals($targetUrl, $payload['targetUrl']);
    $this->assertArrayNotHasKey('shortCode', $payload);
  }
}
