<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Share\Application\Service\ShareService;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Service\ShareFeatureHandlerInterface;
use Slink\Share\Domain\Share;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Domain\ValueObject\ShareContext;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Share\Infrastructure\ReadModel\View\ShortUrlView;
use Slink\Shared\Domain\ValueObject\ID;

final class ShareServiceTest extends TestCase {
  private const string ORIGIN = 'https://example.com';

  private function createSupportingHandler(): ShareFeatureHandlerInterface {
    $handler = $this->createStub(ShareFeatureHandlerInterface::class);
    $handler->method('supports')->willReturn(true);
    $handler->method('enhance')->willReturnArgument(0);

    return $handler;
  }

  #[Test]
  public function itBuildsContextWithFeatureHandlers(): void {
    $shareable = ShareableReference::forImage(ID::generate());

    $handler1 = $this->createStub(ShareFeatureHandlerInterface::class);
    $handler1->method('supports')->willReturn(true);
    $handler1->method('enhance')->willReturnCallback(
      fn(ShareContext $ctx) => $ctx->withShortUrl(ID::generate(), 'abc123')
    );

    $service = new ShareService(self::ORIGIN, [$handler1]);

    $context = $service->buildContext($shareable);

    $this->assertTrue($context->hasShortUrl());
    $this->assertEquals('abc123', $context->getShortCode());
  }

  #[Test]
  public function itSkipsUnsupportedFeatureHandlers(): void {
    $shareable = ShareableReference::forImage(ID::generate());

    $handler1 = $this->createMock(ShareFeatureHandlerInterface::class);
    $handler1->method('supports')->willReturn(false);
    $handler1->expects($this->never())->method('enhance');

    $service = new ShareService(self::ORIGIN, [$handler1]);

    $context = $service->buildContext($shareable);

    $this->assertFalse($context->hasShortUrl());
  }

  #[Test]
  public function itResolvesUrlWithShortCodeForShareView(): void {
    $shortUrlView = $this->createStub(ShortUrlView::class);
    $shortUrlView->method('getShortCode')->willReturn('xyz789');

    $shareable = ShareableReference::forImage(ID::fromString('12345678-1234-1234-1234-123456789abc'));

    $shareView = $this->createStub(ShareView::class);
    $shareView->method('getShortUrl')->willReturn($shortUrlView);
    $shareView->method('getShareable')->willReturn($shareable);
    $shareView->method('getTargetUrl')->willReturn('/image/test.jpg');

    $service = new ShareService(self::ORIGIN, [$this->createSupportingHandler()]);

    $url = $service->resolveUrl($shareView);

    $this->assertEquals('https://example.com/i/xyz789', $url);
  }

  #[Test]
  public function itResolvesUrlWithShortCodeForShare(): void {
    $shareable = ShareableReference::forCollection(ID::fromString('12345678-1234-1234-1234-123456789abc'));

    $share = $this->createStub(Share::class);
    $share->method('getShortCode')->willReturn('col123');
    $share->method('getShareable')->willReturn($shareable);
    $share->method('getTargetUrl')->willReturn('/collection/test');

    $service = new ShareService(self::ORIGIN, [$this->createSupportingHandler()]);

    $url = $service->resolveUrl($share);

    $this->assertEquals('https://example.com/c/col123', $url);
  }

  #[Test]
  public function itResolvesUrlWithTargetUrlWhenNoShortCode(): void {
    $shareable = ShareableReference::forImage(ID::fromString('12345678-1234-1234-1234-123456789abc'));

    $shareView = $this->createStub(ShareView::class);
    $shareView->method('getShortUrl')->willReturn(null);
    $shareView->method('getShareable')->willReturn($shareable);
    $shareView->method('getTargetUrl')->willReturn('/image/test.jpg');

    $service = new ShareService(self::ORIGIN, []);

    $url = $service->resolveUrl($shareView);

    $this->assertEquals('https://example.com/image/test.jpg', $url);
  }

  #[Test]
  public function itUsesCorrectPrefixForDifferentShareableTypes(): void {
    $service = new ShareService(self::ORIGIN, [$this->createSupportingHandler()]);

    $imageShareable = ShareableReference::forImage(ID::generate());
    $collectionShareable = ShareableReference::forCollection(ID::generate());

    $imageShare = $this->createStub(Share::class);
    $imageShare->method('getShortCode')->willReturn('img123');
    $imageShare->method('getShareable')->willReturn($imageShareable);

    $collectionShare = $this->createStub(Share::class);
    $collectionShare->method('getShortCode')->willReturn('col456');
    $collectionShare->method('getShareable')->willReturn($collectionShareable);

    $this->assertStringContainsString('/i/', $service->resolveUrl($imageShare));
    $this->assertStringContainsString('/c/', $service->resolveUrl($collectionShare));
  }
}
