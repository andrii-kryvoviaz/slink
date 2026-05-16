<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Infrastructure\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Infrastructure\Service\PublicImageUrlBuilder;

final class PublicImageUrlBuilderTest extends TestCase {
  #[Test]
  public function itBuildsPublicImageUrl(): void {
    $builder = new PublicImageUrlBuilder();

    $url = $builder->build('image-id', 'image-id.jpg');

    $this->assertSame('/api/image/public/image-id.jpg', $url);
  }

  #[Test]
  public function itPreservesExtensionFromFileName(): void {
    $builder = new PublicImageUrlBuilder();

    $url = $builder->build('image-id', 'photo.webp');

    $this->assertStringEndsWith('.webp', $url);
  }
}
