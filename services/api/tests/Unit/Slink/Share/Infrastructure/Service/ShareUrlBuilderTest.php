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
  private ImageUrlSignatureInterface $signatureService;
  private ShareUrlBuilder $builder;

  protected function setUp(): void {
    $this->signatureService = $this->createStub(ImageUrlSignatureInterface::class);
    $this->builder = new ShareUrlBuilder($this->signatureService);
  }

  #[Test]
  public function itBuildsUrlWithAllParams(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $fileName = 'test.jpg';
    $width = 800;
    $height = 600;
    $crop = true;

    $signatureService = $this->createMock(ImageUrlSignatureInterface::class);
    $signatureService
      ->expects($this->once())
      ->method('sign')
      ->with($imageId, ['width' => $width, 'height' => $height, 'crop' => $crop])
      ->willReturn('test_signature');

    $builder = new ShareUrlBuilder($signatureService);
    $result = $builder->buildTargetUrl($imageId, $fileName, $width, $height, $crop);

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

    $signatureService = $this->createMock(ImageUrlSignatureInterface::class);
    $signatureService
      ->expects($this->once())
      ->method('sign')
      ->with($imageId, ['width' => $width])
      ->willReturn('test_signature');

    $builder = new ShareUrlBuilder($signatureService);
    $result = $builder->buildTargetUrl($imageId, $fileName, $width, null, false);

    $this->assertStringContainsString('width=800', $result);
    $this->assertStringNotContainsString('height=', $result);
    $this->assertStringNotContainsString('crop=', $result);
  }

  #[Test]
  public function itBuildsUrlWithHeightOnly(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $fileName = 'test.jpg';
    $height = 600;

    $signatureService = $this->createMock(ImageUrlSignatureInterface::class);
    $signatureService
      ->expects($this->once())
      ->method('sign')
      ->with($imageId, ['height' => $height])
      ->willReturn('test_signature');

    $builder = new ShareUrlBuilder($signatureService);
    $result = $builder->buildTargetUrl($imageId, $fileName, null, $height, false);

    $this->assertStringContainsString('height=600', $result);
    $this->assertStringNotContainsString('width=', $result);
  }

  #[Test]
  public function itBuildsUrlWithoutParamsWhenNoneProvided(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $fileName = 'test.jpg';

    $signatureService = $this->createMock(ImageUrlSignatureInterface::class);
    $signatureService
      ->expects($this->never())
      ->method('sign');

    $builder = new ShareUrlBuilder($signatureService);
    $result = $builder->buildTargetUrl($imageId, $fileName, null, null, false);

    $this->assertEquals('/image/test.jpg', $result);
  }

  #[Test]
  public function itFiltersOutFalseCropValue(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $fileName = 'test.jpg';
    $width = 800;

    $signatureService = $this->createMock(ImageUrlSignatureInterface::class);
    $signatureService
      ->expects($this->once())
      ->method('sign')
      ->with($imageId, ['width' => $width])
      ->willReturn('test_signature');

    $builder = new ShareUrlBuilder($signatureService);
    $result = $builder->buildTargetUrl($imageId, $fileName, $width, null, false);

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

  #[Test]
  public function itBuildsUrlWithFormatConversion(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $fileName = 'test.png';

    $signatureService = $this->createMock(ImageUrlSignatureInterface::class);
    $signatureService
      ->expects($this->never())
      ->method('sign');

    $builder = new ShareUrlBuilder($signatureService);
    $result = $builder->buildTargetUrl($imageId, $fileName, null, null, false, 'webp');

    $this->assertEquals('/image/test.webp', $result);
  }

  #[Test]
  public function itBuildsUrlWithFormatAndResizeParams(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $fileName = 'test.gif';
    $width = 800;
    $height = 600;

    $signatureService = $this->createMock(ImageUrlSignatureInterface::class);
    $signatureService
      ->expects($this->once())
      ->method('sign')
      ->with($imageId, ['width' => $width, 'height' => $height])
      ->willReturn('test_signature');

    $builder = new ShareUrlBuilder($signatureService);
    $result = $builder->buildTargetUrl($imageId, $fileName, $width, $height, false, 'avif');

    $this->assertStringContainsString('/image/test.avif', $result);
    $this->assertStringContainsString('width=800', $result);
    $this->assertStringContainsString('height=600', $result);
    $this->assertStringContainsString('s=test_signature', $result);
  }

  #[Test]
  public function itIgnoresNullFormat(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $fileName = 'test.jpg';

    $result = $this->builder->buildTargetUrl($imageId, $fileName, null, null, false, null);

    $this->assertEquals('/image/test.jpg', $result);
  }

  #[Test]
  public function itTreatsOriginalFormatAsNoConversion(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $fileName = 'test.png';

    $result = $this->builder->buildTargetUrl($imageId, $fileName, null, null, false, 'original');

    $this->assertEquals('/image/test.png', $result);
  }
}
