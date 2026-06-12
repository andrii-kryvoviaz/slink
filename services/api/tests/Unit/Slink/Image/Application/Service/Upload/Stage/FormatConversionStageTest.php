<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service\Upload\Stage;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\Upload\Stage\FormatConversionStage;
use Slink\Image\Domain\Enum\ImageFormat;
use Slink\Image\Domain\Service\ImageConversionResolverInterface;
use Slink\Image\Domain\Service\ImageFileTransformerInterface;
use Slink\Image\Domain\ValueObject\ImageConversionOptions;
use Symfony\Component\HttpFoundation\File\File;
use Tests\Unit\Slink\Image\Application\Service\Upload\UploadContextFactoryTrait;

class FormatConversionStageTest extends TestCase {
  use UploadContextFactoryTrait;

  /**
   * @return array<string, array{bool}>
   */
  public static function stripFlagProvider(): array {
    return [
      'strip resolved to true' => [true],
      'strip resolved to false' => [false],
    ];
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itReturnsContextUnchangedWhenNoConversionResolved(): void {
    $conversionResolver = $this->createStub(ImageConversionResolverInterface::class);
    $imageTransformer = $this->createMock(ImageFileTransformerInterface::class);

    $stage = new FormatConversionStage($conversionResolver, $imageTransformer);

    $file = $this->createStub(File::class);

    $conversionResolver->method('resolve')->willReturn(null);

    $imageTransformer->expects($this->never())->method('convertToFormat');

    $context = $this->contextWithFile($file);
    $result = $stage->process($context);

    $this->assertSame($file, $result->file());
  }

  /**
   * @throws Exception
   */
  #[Test]
  #[DataProvider('stripFlagProvider')]
  public function itConvertsWithStripFlagFromContext(bool $strip): void {
    $conversionResolver = $this->createStub(ImageConversionResolverInterface::class);
    $imageTransformer = $this->createMock(ImageFileTransformerInterface::class);

    $stage = new FormatConversionStage($conversionResolver, $imageTransformer);

    $file = $this->createStub(File::class);
    $converted = $this->createStub(File::class);

    $conversionResolver->method('resolve')->willReturn(ImageFormat::WEBP);

    $imageTransformer->expects($this->once())
      ->method('convertToFormat')
      ->with($file, $this->callback(
        static fn(ImageConversionOptions $options): bool => $options->format === ImageFormat::WEBP
          && $options->quality === null
          && $options->stripMetadata === $strip,
      ))
      ->willReturn($converted);

    $context = $this->contextWithStripExifMetadata($file, $strip);
    $result = $stage->process($context);

    $this->assertSame($converted, $result->file());
  }
}
