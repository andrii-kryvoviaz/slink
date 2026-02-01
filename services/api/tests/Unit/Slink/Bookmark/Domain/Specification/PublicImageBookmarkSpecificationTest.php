<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Bookmark\Domain\Specification;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Bookmark\Domain\Exception\PrivateImageBookmarkException;
use Slink\Bookmark\Domain\Specification\PublicImageBookmarkSpecification;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\ValueObject\ID;

final class PublicImageBookmarkSpecificationTest extends TestCase {
  #[Test]
  public function itThrowsExceptionWhenImageIsNotPublic(): void {
    $this->expectException(PrivateImageBookmarkException::class);
    $this->expectExceptionMessage('Cannot bookmark a private image');

    $imageId = ID::generate();

    $attributes = $this->createMock(ImageAttributes::class);
    $attributes
      ->expects($this->once())
      ->method('isPublic')
      ->willReturn(false);

    $image = $this->createMock(ImageView::class);
    $image
      ->expects($this->once())
      ->method('getAttributes')
      ->willReturn($attributes);

    $repository = $this->createMock(ImageRepositoryInterface::class);
    $repository
      ->expects($this->once())
      ->method('oneById')
      ->with($imageId->toString())
      ->willReturn($image);

    $specification = new PublicImageBookmarkSpecification($repository);
    $specification->ensureImageIsPublic($imageId);
  }

  #[Test]
  public function itPassesWhenImageIsPublic(): void {
    $imageId = ID::generate();

    $attributes = $this->createMock(ImageAttributes::class);
    $attributes
      ->expects($this->once())
      ->method('isPublic')
      ->willReturn(true);

    $image = $this->createMock(ImageView::class);
    $image
      ->expects($this->once())
      ->method('getAttributes')
      ->willReturn($attributes);

    $repository = $this->createMock(ImageRepositoryInterface::class);
    $repository
      ->expects($this->once())
      ->method('oneById')
      ->with($imageId->toString())
      ->willReturn($image);

    $specification = new PublicImageBookmarkSpecification($repository);
    $specification->ensureImageIsPublic($imageId);

    $this->addToAssertionCount(1);
  }
}
