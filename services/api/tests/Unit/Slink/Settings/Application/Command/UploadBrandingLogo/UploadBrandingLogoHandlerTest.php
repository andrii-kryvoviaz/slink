<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Settings\Application\Command\UploadBrandingLogo;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Enum\ImageFormat;
use Slink\Image\Domain\Service\ImageFileTransformerInterface;
use Slink\Image\Domain\ValueObject\ImageConversionOptions;
use Slink\Settings\Application\Command\UploadBrandingLogo\UploadBrandingLogoCommand;
use Slink\Settings\Application\Command\UploadBrandingLogo\UploadBrandingLogoHandler;
use Slink\Settings\Domain\Enum\BrandingLogoFormat;
use Slink\Shared\Domain\FileSystem\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Validation;

final class UploadBrandingLogoHandlerTest extends TestCase {
  private const string SVG_CONTENT = '<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"></svg>';

  /**
   * @var array<string>
   */
  private array $tempFiles = [];

  protected function tearDown(): void {
    foreach ($this->tempFiles as $file) {
      if (\is_file($file)) {
        \unlink($file);
      }
    }

    parent::tearDown();
  }

  #[Test]
  public function itConvertsRasterLogoToPngAndStoresIt(): void {
    $original = $this->tempFile('logo.png', $this->pngContent());
    $converted = $this->tempFile('logo-converted.png', $this->pngContent());

    $transformer = $this->createMock(ImageFileTransformerInterface::class);
    $transformer
      ->expects($this->once())
      ->method('convertToFormat')
      ->with(
        $this->callback(fn (File $file): bool => $file->getPathname() === $original->getPathname()),
        $this->callback(fn (ImageConversionOptions $options): bool => $options->format === ImageFormat::PNG),
      )
      ->willReturn($converted);

    $storage = $this->createMock(StorageInterface::class);
    $storage->expects($this->exactly(2))->method('delete');
    $storage
      ->expects($this->once())
      ->method('upload')
      ->with($converted, BrandingLogoFormat::Png->fileName());

    $handler = new UploadBrandingLogoHandler($storage, $transformer);
    $result = $handler(new UploadBrandingLogoCommand($original));

    $expectedHash = \substr(\hash('sha256', $this->pngContent()), 0, 8);
    $this->assertSame(['url' => '/api/branding/logo?v=' . $expectedHash], $result);
  }

  #[Test]
  public function itStoresSvgLogoWithoutConversion(): void {
    $svg = $this->tempFile('logo.svg', self::SVG_CONTENT);

    $transformer = $this->createMock(ImageFileTransformerInterface::class);
    $transformer->expects($this->never())->method('convertToFormat');

    $storage = $this->createMock(StorageInterface::class);
    $storage
      ->expects($this->once())
      ->method('upload')
      ->with($svg, BrandingLogoFormat::Svg->fileName());

    $handler = new UploadBrandingLogoHandler($storage, $transformer);
    $result = $handler(new UploadBrandingLogoCommand($svg));

    $expectedHash = \substr(\hash('sha256', self::SVG_CONTENT), 0, 8);
    $this->assertSame(['url' => '/api/branding/logo?v=' . $expectedHash], $result);
  }

  #[Test]
  public function itRejectsUnsupportedMimeType(): void {
    $text = $this->tempFile('logo.txt', 'definitely not an image');

    $violations = $this->validate(new UploadBrandingLogoCommand($text));

    $this->assertGreaterThan(0, \count($violations));
    $this->assertSame('file', $violations->get(0)->getPropertyPath());
  }

  #[Test]
  public function itRejectsOversizedFile(): void {
    $oversized = $this->tempFile('logo-large.png', $this->pngContent() . \str_repeat('A', 2 * 1024 * 1024));

    $violations = $this->validate(new UploadBrandingLogoCommand($oversized));

    $this->assertGreaterThan(0, \count($violations));
    $this->assertSame('file', $violations->get(0)->getPropertyPath());
  }

  private function validate(UploadBrandingLogoCommand $command): \Symfony\Component\Validator\ConstraintViolationListInterface {
    return Validation::createValidatorBuilder()
      ->enableAttributeMapping()
      ->getValidator()
      ->validate($command);
  }

  private function tempFile(string $name, string $content): File {
    $path = \sys_get_temp_dir() . '/slink_branding_' . \uniqid() . '_' . $name;
    \file_put_contents($path, $content);
    $this->tempFiles[] = $path;

    return new File($path);
  }

  private function pngContent(): string {
    $png = \base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);

    if ($png === false) {
      throw new \RuntimeException('Unable to decode test png image.');
    }

    return $png;
  }
}
