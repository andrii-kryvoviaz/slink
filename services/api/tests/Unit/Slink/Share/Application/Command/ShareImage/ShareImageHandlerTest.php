<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Application\Command\ShareImage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Share\Application\Command\ShareImage\ShareImageCommand;
use Slink\Share\Application\Command\ShareImage\ShareImageHandler;
use Slink\Share\Domain\Repository\ShareStoreRepositoryInterface;
use Slink\Share\Domain\Service\ShortCodeGeneratorInterface;
use Slink\Share\Domain\Share;

final class ShareImageHandlerTest extends TestCase {
  private MockObject&ShareStoreRepositoryInterface $shareStore;
  private MockObject&ShortCodeGeneratorInterface $shortCodeGenerator;
  private ShareImageHandler $handler;

  protected function setUp(): void {
    $this->shareStore = $this->createMock(ShareStoreRepositoryInterface::class);
    $this->shortCodeGenerator = $this->createMock(ShortCodeGeneratorInterface::class);
    $this->handler = new ShareImageHandler($this->shareStore, $this->shortCodeGenerator);
  }

  #[Test]
  public function itCreatesShareWithShortUrl(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $targetUrl = '/image/test.jpg?width=800&height=600&s=signature';
    $shortCode = 'aBcD1234';

    $command = new ShareImageCommand($imageId, $targetUrl, true);

    $this->shortCodeGenerator
      ->expects($this->once())
      ->method('generate')
      ->willReturn($shortCode);

    $this->shareStore
      ->expects($this->once())
      ->method('store')
      ->with($this->isInstanceOf(Share::class));

    $result = ($this->handler)($command);

    $this->assertInstanceOf(Share::class, $result);
    $this->assertEquals($imageId, $result->getImageId()->toString());
    $this->assertEquals($targetUrl, $result->getTargetUrl());
    $this->assertEquals($shortCode, $result->getShortCode());
  }

  #[Test]
  public function itCreatesShareWithoutShortUrlWhenDisabled(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $targetUrl = '/image/test.jpg';

    $command = new ShareImageCommand($imageId, $targetUrl, false);

    $this->shortCodeGenerator
      ->expects($this->never())
      ->method('generate');

    $this->shareStore
      ->expects($this->once())
      ->method('store')
      ->with($this->isInstanceOf(Share::class));

    $result = ($this->handler)($command);

    $this->assertInstanceOf(Share::class, $result);
    $this->assertNull($result->getShortCode());
  }

  #[Test]
  public function itDefaultsToCreatingShortUrl(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $targetUrl = '/image/test.jpg';
    $shortCode = 'xYz98765';

    $command = new ShareImageCommand($imageId, $targetUrl);

    $this->shortCodeGenerator
      ->expects($this->once())
      ->method('generate')
      ->willReturn($shortCode);

    $this->shareStore
      ->expects($this->once())
      ->method('store');

    $result = ($this->handler)($command);

    $this->assertEquals($shortCode, $result->getShortCode());
  }
}
