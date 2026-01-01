<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\ImageUrlService;
use Slink\Share\Domain\Enum\ShareType;
use Slink\Share\Domain\Service\ShareServiceInterface;
use Slink\Share\Domain\ValueObject\ShareResult;

final class ImageUrlServiceTest extends TestCase {
  private MockObject&ShareServiceInterface $shareService;
  private ImageUrlService $imageUrlService;

  protected function setUp(): void {
    $this->shareService = $this->createMock(ShareServiceInterface::class);
    $this->imageUrlService = new ImageUrlService($this->shareService);
  }

  #[Test]
  public function itGeneratesRegularImageUrlWhenShorteningDisabled(): void {
    $fileName = '12345678-1234-1234-1234-123456789abc.jpg';
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $targetUrl = "/image/{$fileName}";

    $result = ShareResult::signed($targetUrl);

    $this->shareService
      ->expects($this->once())
      ->method('share')
      ->with($imageId, $targetUrl)
      ->willReturn($result);

    $url = $this->imageUrlService->generateImageUrl($fileName);

    $this->assertEquals($targetUrl, $url);
  }

  #[Test]
  public function itGeneratesShortUrlWhenShorteningEnabled(): void {
    $fileName = '12345678-1234-1234-1234-123456789abc.jpg';
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $targetUrl = "/image/{$fileName}";
    $shortCode = 'aBcD1234';

    $result = ShareResult::shortUrl($shortCode);

    $this->shareService
      ->expects($this->once())
      ->method('share')
      ->with($imageId, $targetUrl)
      ->willReturn($result);

    $url = $this->imageUrlService->generateImageUrl($fileName);

    $this->assertEquals("i/{$shortCode}", $url);
  }

  #[Test]
  public function itGeneratesRegularThumbnailUrlWhenShorteningDisabled(): void {
    $fileName = '12345678-1234-1234-1234-123456789abc.jpg';
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $width = 300;
    $height = 300;
    $targetUrl = "/image/{$fileName}?width={$width}&height={$height}&crop=true";

    $result = ShareResult::signed($targetUrl);

    $this->shareService
      ->expects($this->once())
      ->method('share')
      ->with($imageId, $targetUrl)
      ->willReturn($result);

    $url = $this->imageUrlService->generateThumbnailUrl($fileName, $width, $height);

    $this->assertEquals($targetUrl, $url);
  }

  #[Test]
  public function itGeneratesShortThumbnailUrlWhenShorteningEnabled(): void {
    $fileName = '12345678-1234-1234-1234-123456789abc.jpg';
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $width = 800;
    $height = 600;
    $targetUrl = "/image/{$fileName}?width={$width}&height={$height}&crop=true";
    $shortCode = 'xYz98765';

    $result = ShareResult::shortUrl($shortCode);

    $this->shareService
      ->expects($this->once())
      ->method('share')
      ->with($imageId, $targetUrl)
      ->willReturn($result);

    $url = $this->imageUrlService->generateThumbnailUrl($fileName, $width, $height);

    $this->assertEquals("i/{$shortCode}", $url);
  }

  #[Test]
  public function itExtractsImageIdFromFileName(): void {
    $fileName = 'abc123-def456-ghi789.png';
    $expectedImageId = 'abc123-def456-ghi789';
    $targetUrl = "/image/{$fileName}";

    $result = ShareResult::signed($targetUrl);

    $this->shareService
      ->expects($this->once())
      ->method('share')
      ->with($expectedImageId, $targetUrl)
      ->willReturn($result);

    $this->imageUrlService->generateImageUrl($fileName);
  }

  #[Test]
  public function itUsesThumbnailDefaultParameters(): void {
    $fileName = '12345678-1234-1234-1234-123456789abc.jpg';
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $targetUrl = "/image/{$fileName}?width=300&height=300&crop=true";

    $result = ShareResult::signed($targetUrl);

    $this->shareService
      ->expects($this->once())
      ->method('share')
      ->with($imageId, $targetUrl)
      ->willReturn($result);

    $url = $this->imageUrlService->generateThumbnailUrl($fileName);

    $this->assertEquals($targetUrl, $url);
  }
}
