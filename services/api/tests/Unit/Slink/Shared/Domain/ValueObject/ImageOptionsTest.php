<?php

declare(strict_types=1);

namespace Slink\Tests\Unit\Slink\Shared\Domain\ValueObject;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ImageOptions;

final class ImageOptionsTest extends TestCase {
  #[Test]
  public function itKeepsOriginalExtensionWhenFormatIsNotSet(): void {
    $options = ImageOptions::fromPayload([
      'fileName' => 'photo.png',
      'mimeType' => 'image/png',
      'width' => 400,
    ]);

    $this->assertStringEndsWith('.png', $options->getCacheFileName());
  }

  #[Test]
  public function itUsesRequestedFormatAsCacheExtension(): void {
    $options = ImageOptions::fromPayload([
      'fileName' => 'photo.png',
      'mimeType' => 'image/png',
      'format' => 'avif',
    ]);

    $this->assertStringEndsWith('.avif', $options->getCacheFileName());
    $this->assertStringContainsString('photo-', $options->getCacheFileName());
  }

  #[Test]
  public function itUsesRequestedFormatEvenWhenSourceDiffers(): void {
    $options = ImageOptions::fromPayload([
      'fileName' => 'animation.gif',
      'mimeType' => 'image/gif',
      'format' => 'webp',
      'quality' => 80,
    ]);

    $cacheFileName = $options->getCacheFileName();

    $this->assertStringEndsWith('.webp', $cacheFileName);
    $this->assertStringNotContainsString('.gif', $cacheFileName);
  }
}
