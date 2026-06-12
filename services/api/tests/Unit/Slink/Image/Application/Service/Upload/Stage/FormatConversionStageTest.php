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
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\User\Domain\Enum\ExifMetadataPreference;
use Slink\User\Domain\ValueObject\UserPreferences;
use Symfony\Component\HttpFoundation\File\File;
use Tests\Unit\Slink\Image\Application\Service\Upload\UploadContextFactoryTrait;

class FormatConversionStageTest extends TestCase {
  use UploadContextFactoryTrait;

  /**
   * @return array<string, array{bool, ExifMetadataPreference, bool}>
   */
  public static function stripPreferenceMatrixProvider(): array {
    return [
      'server strip, keep override disables strip' => [true, ExifMetadataPreference::Keep, false],
      'server strip, default follows server' => [true, ExifMetadataPreference::Default, true],
      'server keep, strip override wins' => [false, ExifMetadataPreference::Strip, true],
    ];
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itReturnsContextUnchangedWhenNoConversionResolved(): void {
    $conversionResolver = $this->createStub(ImageConversionResolverInterface::class);
    $imageTransformer = $this->createMock(ImageFileTransformerInterface::class);
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);

    $stage = new FormatConversionStage($conversionResolver, $imageTransformer, $configProvider);

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
  public function itConvertsFileWhenTargetFormatResolved(): void {
    $conversionResolver = $this->createStub(ImageConversionResolverInterface::class);
    $imageTransformer = $this->createMock(ImageFileTransformerInterface::class);
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);

    $stage = new FormatConversionStage($conversionResolver, $imageTransformer, $configProvider);

    $file = $this->createStub(File::class);
    $converted = $this->createStub(File::class);

    $conversionResolver->method('resolve')->willReturn(ImageFormat::WEBP);

    $configProvider->method('get')->willReturnMap([
      ['image.stripExifMetadata', true],
    ]);

    $imageTransformer->expects($this->once())
      ->method('convertToFormat')
      ->with($file, ImageFormat::WEBP)
      ->willReturn($converted);

    $context = $this->contextWithPreferences($file, UserPreferences::create());
    $result = $stage->process($context);

    $this->assertSame($converted, $result->file());
  }

  /**
   * @throws Exception
   */
  #[Test]
  #[DataProvider('stripPreferenceMatrixProvider')]
  public function itConvertsWithStripResolvedFromPreference(bool $serverStrip, ExifMetadataPreference $preference, bool $expectedStrip): void {
    $conversionResolver = $this->createStub(ImageConversionResolverInterface::class);
    $imageTransformer = $this->createMock(ImageFileTransformerInterface::class);
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);

    $stage = new FormatConversionStage($conversionResolver, $imageTransformer, $configProvider);

    $file = $this->createStub(File::class);
    $converted = $this->createStub(File::class);

    $conversionResolver->method('resolve')->willReturn(ImageFormat::WEBP);

    $configProvider->method('get')->willReturnMap([
      ['image.stripExifMetadata', $serverStrip],
    ]);

    $imageTransformer->expects($this->once())
      ->method('convertToFormat')
      ->with($file, ImageFormat::WEBP, null, $expectedStrip)
      ->willReturn($converted);

    $preferences = UserPreferences::create(exifMetadataPreference: $preference);
    $context = $this->contextWithPreferences($file, $preferences);
    $result = $stage->process($context);

    $this->assertSame($converted, $result->file());
  }
}
