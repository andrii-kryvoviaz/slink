<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service\Upload\Stage;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\Upload\Stage\ExifStrippingStage;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageFileTransformerInterface;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\User\Domain\Enum\ExifMetadataPreference;
use Slink\User\Domain\ValueObject\UserPreferences;
use Symfony\Component\HttpFoundation\File\File;
use Tests\Unit\Slink\Image\Application\Service\Upload\UploadContextFactoryTrait;

class ExifStrippingStageTest extends TestCase {
  use UploadContextFactoryTrait;

  /**
   * @return array<string, array{bool, ExifMetadataPreference, bool}>
   */
  public static function exifPreferenceMatrixProvider(): array {
    return [
      'server strip, default follows server' => [true, ExifMetadataPreference::Default, true],
      'server keep, default follows server' => [false, ExifMetadataPreference::Default, false],
      'server strip, strip override wins' => [true, ExifMetadataPreference::Strip, true],
      'server keep, strip override wins' => [false, ExifMetadataPreference::Strip, true],
      'server strip, keep override wins' => [true, ExifMetadataPreference::Keep, false],
      'server keep, keep override wins' => [false, ExifMetadataPreference::Keep, false],
    ];
  }

  /**
   * @throws Exception
   */
  #[Test]
  #[DataProvider('exifPreferenceMatrixProvider')]
  public function itStripsWhenPreferenceResolvesToStripAndProfileSupported(bool $serverStrip, ExifMetadataPreference $preference, bool $expectsStrip): void {
    $imageAnalyzer = $this->createStub(ImageAnalyzerInterface::class);
    $imageTransformer = $this->createMock(ImageFileTransformerInterface::class);
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);

    $stage = new ExifStrippingStage($imageAnalyzer, $imageTransformer, $configProvider);

    $file = $this->createStub(File::class);
    $file->method('getMimeType')->willReturn('image/jpeg');
    $file->method('getPathname')->willReturn('/tmp/test.jpg');

    $imageAnalyzer->method('supportsExifProfile')->willReturn(true);

    $configProvider->method('get')->willReturnMap([
      ['image.stripExifMetadata', $serverStrip],
    ]);

    $expectedStripCalls = $expectsStrip
      ? $this->once()
      : $this->never();
    $imageTransformer->expects($expectedStripCalls)
      ->method('stripExifMetadata')
      ->with('/tmp/test.jpg');

    $preferences = UserPreferences::create(exifMetadataPreference: $preference);
    $context = $this->contextWithPreferences($file, $preferences);

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
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);

    $stage = new ExifStrippingStage($imageAnalyzer, $imageTransformer, $configProvider);

    $file = $this->createStub(File::class);
    $file->method('getMimeType')->willReturn('image/svg+xml');
    $file->method('getPathname')->willReturn('/tmp/test.svg');

    $imageAnalyzer->method('supportsExifProfile')->willReturn(false);

    $configProvider->method('get')->willReturnMap([
      ['image.stripExifMetadata', true],
    ]);

    $imageTransformer->expects($this->never())->method('stripExifMetadata');

    $preferences = UserPreferences::create(exifMetadataPreference: ExifMetadataPreference::Strip);
    $context = $this->contextWithPreferences($file, $preferences);

    $result = $stage->process($context);

    $this->assertSame($file, $result->file());
  }
}
