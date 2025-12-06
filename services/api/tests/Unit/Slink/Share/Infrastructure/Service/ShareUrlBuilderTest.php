<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Infrastructure\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Service\ImageUrlSignatureInterface;
use Slink\Share\Infrastructure\Service\ShareUrlBuilder;

final class ShareUrlBuilderTest extends TestCase {
  private MockObject&ImageUrlSignatureInterface $signatureService;
  private ShareUrlBuilder $builder;

  protected function setUp(): void {
    $this->signatureService = $this->createMock(ImageUrlSignatureInterface::class);
    $this->builder = new ShareUrlBuilder($this->signatureService);
  }

  #[Test]
  public function itBuildsUrlWithAllParams(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $fileName = 'test.jpg';
    $width = 800;
    $height = 600;
    $crop = true;

    $this->signatureService
      ->expects($this->once())
      ->method('sign')
      ->with($imageId, ['width' => $width, 'height' => $height, 'crop' => $crop])
      ->willReturn('test_signature');

    $result = $this->builder->buildTargetUrl($imageId, $fileName, $width, $height, $crop);

    $this->assertStringContainsString('/image/test.jpg', $result);
    $this->assertStringContainsString('width=800', $result);
    $this->assertStringContainsString('height=600', $result);
    $this->assertStringContainsString('crop=1', $result);
    $this->assertStringContainsString('s=test_signature', $result);
  }

  #[Test]
  public function itBuildsUrlWithWidthOnly(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $fileName = 'test.jpg';
    $width = 800;

    $this->signatureService
      ->expects($this->once())
      ->method('sign')
      ->with($imageId, ['width' => $width])
      ->willReturn('test_signature');

    $result = $this->builder->buildTargetUrl($imageId, $fileName, $width, null, false);

    $this->assertStringContainsString('width=800', $result);
    $this->assertStringNotContainsString('height=', $result);
    $this->assertStringNotContainsString('crop=', $result);
  }

  #[Test]
  public function itBuildsUrlWithHeightOnly(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $fileName = 'test.jpg';
    $height = 600;

    $this->signatureService
      ->expects($this->once())
      ->method('sign')
      ->with($imageId, ['height' => $height])
      ->willReturn('test_signature');

    $result = $this->builder->buildTargetUrl($imageId, $fileName, null, $height, false);

    $this->assertStringContainsString('height=600', $result);
    $this->assertStringNotContainsString('width=', $result);
  }

  #[Test]
  public function itBuildsUrlWithoutParamsWhenNoneProvided(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $fileName = 'test.jpg';

    $this->signatureService
      ->expects($this->never())
      ->method('sign');

    $result = $this->builder->buildTargetUrl($imageId, $fileName, null, null, false);

    $this->assertEquals('/image/test.jpg', $result);
  }

  #[Test]
  public function itFiltersOutFalseCropValue(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $fileName = 'test.jpg';
    $width = 800;

    $this->signatureService
      ->expects($this->once())
      ->method('sign')
      ->with($imageId, ['width' => $width])
      ->willReturn('test_signature');

    $result = $this->builder->buildTargetUrl($imageId, $fileName, $width, null, false);

    $this->assertStringNotContainsString('crop=', $result);
  }

  #[Test]
  #[DataProvider('fileNameProvider')]
  public function itHandlesVariousFileNames(string $fileName, string $expectedPath): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';

    $result = $this->builder->buildTargetUrl($imageId, $fileName, null, null, false);

    $this->assertEquals($expectedPath, $result);
  }

  /**
   * @return array<string, array{string, string}>
   */
  public static function fileNameProvider(): array {
    return [
      'jpg file' => ['image.jpg', '/image/image.jpg'],
      'png file' => ['photo.png', '/image/photo.png'],
      'webp file' => ['picture.webp', '/image/picture.webp'],
      'uuid filename' => ['12345678-1234-1234-1234-123456789abc.avif', '/image/12345678-1234-1234-1234-123456789abc.avif'],
    ];
  }
}
