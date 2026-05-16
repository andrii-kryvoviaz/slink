<?php

declare(strict_types=1);

namespace Unit\Slink\Image\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\ImageContentLoader;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageRetrievalInterface;
use Slink\Image\Domain\Service\ImageSanitizerInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Slink\Shared\Infrastructure\Exception\NotFoundException;

final class ImageContentLoaderTest extends TestCase {
  private ImageAnalyzerInterface&Stub $imageAnalyzer;
  private ImageRetrievalInterface&Stub $imageRetrieval;
  private ImageSanitizerInterface&Stub $sanitizer;

  public function setUp(): void {
    parent::setUp();

    $this->imageAnalyzer = $this->createStub(ImageAnalyzerInterface::class);
    $this->imageRetrieval = $this->createStub(ImageRetrievalInterface::class);
    $this->sanitizer = $this->createStub(ImageSanitizerInterface::class);

    $this->imageAnalyzer->method('supportsResize')->willReturn(true);
    $this->sanitizer->method('requiresSanitization')->willReturn(false);
  }

  private function imageView(string $fileName, string $mimeType, string $uuid = 'image-uuid'): ImageView&Stub {
    $imageView = $this->createStub(ImageView::class);
    $imageView->method('getFileName')->willReturn($fileName);
    $imageView->method('getMimeType')->willReturn($mimeType);
    $imageView->method('getUuid')->willReturn($uuid);

    return $imageView;
  }

  private function createLoader(): ImageContentLoader {
    return new ImageContentLoader(
      $this->imageAnalyzer,
      $this->imageRetrieval,
      $this->sanitizer,
    );
  }

  #[Test]
  public function itReturnsContentAndMimeTypeFromRetrieval(): void {
    $this->imageRetrieval->method('getImage')->willReturn('binary-bytes');

    $payload = $this->createLoader()->load(
      $this->imageView('test-file.jpg', 'image/jpeg'),
    );

    $this->assertSame('binary-bytes', $payload->content);
    $this->assertSame('image/jpeg', $payload->mimeType);
  }

  #[Test]
  public function itThrowsNotFoundWhenRetrievalReturnsNull(): void {
    $this->imageRetrieval->method('getImage')->willReturn(null);

    $this->expectException(NotFoundException::class);

    $this->createLoader()->load(
      $this->imageView('missing.jpg', 'image/jpeg'),
    );
  }

  #[Test]
  public function itAppliesTransformsAsProvided(): void {
    $captured = null;
    $imageRetrieval = $this->createStub(ImageRetrievalInterface::class);
    $imageRetrieval
      ->method('getImage')
      ->willReturnCallback(function (ImageOptions $options) use (&$captured): string {
        $captured = $options->toPayload();
        return 'bytes';
      });

    $loader = new ImageContentLoader(
      $this->imageAnalyzer,
      $imageRetrieval,
      $this->sanitizer,
    );

    $loader->load(
      $this->imageView('img.jpg', 'image/jpeg'),
      transforms: ['width' => 200, 'height' => 100, 'quality' => 80, 'crop' => false, 'filter' => null],
    );

    $this->assertIsArray($captured);
    $this->assertSame(200, $captured['width']);
    $this->assertSame(100, $captured['height']);
    $this->assertSame(80, $captured['quality']);
  }

  #[Test]
  public function itTriggersFormatConversionWhenRequestedDiffersFromOriginal(): void {
    $this->imageRetrieval->method('getImage')->willReturn('converted');

    $payload = $this->createLoader()->load(
      $this->imageView('img.png', 'image/png'),
      'webp',
    );

    $this->assertSame('image/webp', $payload->mimeType);
    $this->assertSame('converted', $payload->content);
  }

  #[Test]
  public function itDoesNotTriggerFormatConversionWhenRequestedMatchesOriginal(): void {
    $this->imageRetrieval->method('getImage')->willReturn('original');

    $payload = $this->createLoader()->load(
      $this->imageView('img.gif', 'image/gif'),
      'gif',
    );

    $this->assertSame('image/gif', $payload->mimeType);
  }

  #[Test]
  public function itHandlesJpegJpgAliasesWithoutConversion(): void {
    $this->imageRetrieval->method('getImage')->willReturn('jpeg-bytes');

    $payload = $this->createLoader()->load(
      $this->imageView('img.jpeg', 'image/jpeg'),
      'jpg',
    );

    $this->assertSame('image/jpeg', $payload->mimeType);
  }

  #[Test]
  public function itSanitizesContentForSensitiveMimeTypes(): void {
    $sanitizer = $this->createStub(ImageSanitizerInterface::class);
    $sanitizer->method('requiresSanitization')->willReturn(true);
    $sanitizer->method('sanitize')->willReturnCallback(static fn (string $content): string => "clean:{$content}");

    $imageRetrieval = $this->createStub(ImageRetrievalInterface::class);
    $imageRetrieval->method('getImage')->willReturn('<svg><script>x</script></svg>');

    $loader = new ImageContentLoader(
      $this->imageAnalyzer,
      $imageRetrieval,
      $sanitizer,
    );

    $payload = $loader->load(
      $this->imageView('img.svg', 'image/svg+xml'),
    );

    $this->assertSame('clean:<svg><script>x</script></svg>', $payload->content);
  }

  #[Test]
  public function itSkipsTransformParamsWhenAnalyzerDoesNotSupportResize(): void {
    $analyzer = $this->createStub(ImageAnalyzerInterface::class);
    $analyzer->method('supportsResize')->willReturn(false);

    $captured = null;
    $imageRetrieval = $this->createStub(ImageRetrievalInterface::class);
    $imageRetrieval
      ->method('getImage')
      ->willReturnCallback(function (ImageOptions $options) use (&$captured): string {
        $captured = $options->toPayload();
        return 'bytes';
      });

    $loader = new ImageContentLoader(
      $analyzer,
      $imageRetrieval,
      $this->sanitizer,
    );

    $loader->load(
      $this->imageView('img.svg', 'image/svg+xml'),
      transforms: ['width' => 200, 'height' => 100, 'quality' => null, 'crop' => false, 'filter' => null],
    );

    $this->assertIsArray($captured);
    $this->assertNull($captured['width']);
    $this->assertNull($captured['height']);
  }
}
