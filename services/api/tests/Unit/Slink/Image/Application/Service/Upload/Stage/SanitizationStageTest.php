<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service\Upload\Stage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\Upload\Stage\SanitizationStage;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageSanitizerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Tests\Unit\Slink\Image\Application\Service\Upload\UploadContextFactoryTrait;

class SanitizationStageTest extends TestCase {
  use UploadContextFactoryTrait;

  /**
   * @throws Exception
   */
  #[Test]
  public function itReturnsContextUnchangedWhenSanitizationNotRequired(): void {
    $imageAnalyzer = $this->createStub(ImageAnalyzerInterface::class);
    $sanitizer = $this->createMock(ImageSanitizerInterface::class);

    $stage = new SanitizationStage($imageAnalyzer, $sanitizer);

    $file = $this->createStub(File::class);
    $file->method('getMimeType')->willReturn('image/jpeg');

    $imageAnalyzer->method('requiresSanitization')->willReturn(false);

    $sanitizer->expects($this->never())->method('sanitizeFile');

    $context = $this->contextWithFile($file);
    $result = $stage->process($context);

    $this->assertSame($file, $result->file());
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itSanitizesFileWhenSanitizationRequired(): void {
    $imageAnalyzer = $this->createStub(ImageAnalyzerInterface::class);
    $sanitizer = $this->createMock(ImageSanitizerInterface::class);

    $stage = new SanitizationStage($imageAnalyzer, $sanitizer);

    $file = $this->createStub(File::class);
    $file->method('getMimeType')->willReturn('image/svg+xml');

    $sanitized = $this->createStub(File::class);

    $imageAnalyzer->method('requiresSanitization')->willReturn(true);

    $sanitizer->expects($this->once())
      ->method('sanitizeFile')
      ->with($file)
      ->willReturn($sanitized);

    $context = $this->contextWithFile($file);
    $result = $stage->process($context);

    $this->assertSame($sanitized, $result->file());
  }
}
