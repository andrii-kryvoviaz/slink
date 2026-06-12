<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service\Upload\Stage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\Upload\Stage\ExifStrippingStage;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageFileTransformerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Tests\Unit\Slink\Image\Application\Service\Upload\UploadContextFactoryTrait;

class ExifStrippingStageTest extends TestCase {
  use UploadContextFactoryTrait;

  /**
   * @throws Exception
   */
  #[Test]
  public function itStripsWhenContextResolvesToStripAndProfileSupported(): void {
    $imageAnalyzer = $this->createStub(ImageAnalyzerInterface::class);
    $imageTransformer = $this->createMock(ImageFileTransformerInterface::class);

    $stage = new ExifStrippingStage($imageAnalyzer, $imageTransformer);

    $file = $this->createStub(File::class);
    $file->method('getMimeType')->willReturn('image/jpeg');
    $file->method('getPathname')->willReturn('/tmp/test.jpg');

    $imageAnalyzer->method('supportsExifProfile')->willReturn(true);

    $imageTransformer->expects($this->once())
      ->method('stripExifMetadata')
      ->with('/tmp/test.jpg');

    $context = $this->contextWithStripExifMetadata($file, true);

    $result = $stage->process($context);

    $this->assertSame($file, $result->file());
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itSkipsWhenContextResolvesToKeep(): void {
    $imageAnalyzer = $this->createStub(ImageAnalyzerInterface::class);
    $imageTransformer = $this->createMock(ImageFileTransformerInterface::class);

    $stage = new ExifStrippingStage($imageAnalyzer, $imageTransformer);

    $file = $this->createStub(File::class);
    $file->method('getMimeType')->willReturn('image/jpeg');
    $file->method('getPathname')->willReturn('/tmp/test.jpg');

    $imageAnalyzer->method('supportsExifProfile')->willReturn(true);

    $imageTransformer->expects($this->never())->method('stripExifMetadata');

    $context = $this->contextWithStripExifMetadata($file, false);

    $result = $stage->process($context);

    $this->assertSame($file, $result->file());
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itNeverStripsWhenProfileUnsupported(): void {
    $imageAnalyzer = $this->createStub(ImageAnalyzerInterface::class);
    $imageTransformer = $this->createMock(ImageFileTransformerInterface::class);

    $stage = new ExifStrippingStage($imageAnalyzer, $imageTransformer);

    $file = $this->createStub(File::class);
    $file->method('getMimeType')->willReturn('image/svg+xml');
    $file->method('getPathname')->willReturn('/tmp/test.svg');

    $imageAnalyzer->method('supportsExifProfile')->willReturn(false);

    $imageTransformer->expects($this->never())->method('stripExifMetadata');

    $context = $this->contextWithStripExifMetadata($file, true);

    $result = $stage->process($context);

    $this->assertSame($file, $result->file());
  }
}
