<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Infrastructure\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Infrastructure\Service\CollectionScopedImageUrlBuilder;

final class CollectionScopedImageUrlBuilderTest extends TestCase {
  #[Test]
  public function itBuildsCollectionScopedItemUrl(): void {
    $builder = new CollectionScopedImageUrlBuilder();

    $url = $builder->build('image-id', 'test.jpg', 'collection-id');

    $this->assertSame(
      '/image/collection/collection-id/items/image-id.jpg',
      $url,
    );
  }

  #[Test]
  public function itPreservesExtensionFromFileName(): void {
    $builder = new CollectionScopedImageUrlBuilder();

    $url = $builder->build('image-id', 'photo.webp', 'collection-id');

    $this->assertStringEndsWith('.webp', $url);
  }
}
