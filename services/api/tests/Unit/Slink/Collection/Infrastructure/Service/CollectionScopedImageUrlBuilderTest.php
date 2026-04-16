<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Infrastructure\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Infrastructure\Service\CollectionScopedImageUrlBuilder;
use Slink\Image\Domain\Service\ImageUrlSignatureInterface;

final class CollectionScopedImageUrlBuilderTest extends TestCase {
  private ImageUrlSignatureInterface&Stub $signature;

  protected function setUp(): void {
    parent::setUp();

    $this->signature = $this->createStub(ImageUrlSignatureInterface::class);
  }

  #[Test]
  public function itBuildsUrlWithCollectionAndSignatureParams(): void {
    $this->signature
      ->method('sign')
      ->willReturn('computed-signature');

    $builder = new CollectionScopedImageUrlBuilder($this->signature);

    $url = $builder->build('image-id', 'test.jpg', 'collection-id');

    $this->assertSame(
      '/image/test.jpg?collection=collection-id&cs=computed-signature',
      $url,
    );
  }

  #[Test]
  public function itSignsWithImageIdAndCollectionPayload(): void {
    $signature = $this->createMock(ImageUrlSignatureInterface::class);
    $signature
      ->expects($this->once())
      ->method('sign')
      ->with('image-id', ['collection' => 'collection-id'])
      ->willReturn('sig');

    $builder = new CollectionScopedImageUrlBuilder($signature);

    $url = $builder->build('image-id', 'name.jpg', 'collection-id');

    $this->assertStringContainsString('cs=sig', $url);
    $this->assertStringContainsString('collection=collection-id', $url);
    $this->assertStringStartsWith('/image/name.jpg?', $url);
  }

  #[Test]
  public function itUrlEncodesQueryParams(): void {
    $this->signature->method('sign')->willReturn('sig/with+chars');

    $builder = new CollectionScopedImageUrlBuilder($this->signature);

    $url = $builder->build('image-id', 'file.jpg', 'collection-id');

    $this->assertStringContainsString('cs=sig%2Fwith%2Bchars', $url);
  }
}
