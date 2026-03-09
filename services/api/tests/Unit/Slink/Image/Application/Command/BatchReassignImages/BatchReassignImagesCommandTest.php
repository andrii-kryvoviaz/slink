<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Command\BatchReassignImages;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\BatchReassignImages\BatchReassignImagesCommand;

final class BatchReassignImagesCommandTest extends TestCase {
  private const IMAGE_ID_1 = '11111111-1111-1111-1111-111111111111';
  private const IMAGE_ID_2 = '22222222-2222-2222-2222-222222222222';
  private const TAG_ID_1 = '33333333-3333-3333-3333-333333333333';
  private const TAG_ID_2 = '44444444-4444-4444-4444-444444444444';
  private const COLLECTION_ID_1 = '55555555-5555-5555-5555-555555555555';
  private const COLLECTION_ID_2 = '66666666-6666-6666-6666-666666666666';

  #[Test]
  public function itReturnsImageIdsFromAssignmentKeys(): void {
    $command = new BatchReassignImagesCommand([
      self::IMAGE_ID_1 => ['tagIds' => [self::TAG_ID_1]],
      self::IMAGE_ID_2 => ['collectionIds' => [self::COLLECTION_ID_1]],
    ]);

    $this->assertSame([self::IMAGE_ID_1, self::IMAGE_ID_2], $command->getImageIds());
  }

  #[Test]
  public function itReturnsTagIdsForSpecificImage(): void {
    $command = new BatchReassignImagesCommand([
      self::IMAGE_ID_1 => ['tagIds' => [self::TAG_ID_1, self::TAG_ID_2]],
    ]);

    $this->assertSame([self::TAG_ID_1, self::TAG_ID_2], $command->getTagIdsForImage(self::IMAGE_ID_1));
  }

  #[Test]
  public function itReturnsNullTagIdsForMissingImage(): void {
    $command = new BatchReassignImagesCommand([
      self::IMAGE_ID_1 => ['collectionIds' => [self::COLLECTION_ID_1]],
    ]);

    $this->assertNull($command->getTagIdsForImage(self::IMAGE_ID_2));
  }

  #[Test]
  public function itReturnsCollectionIdsForSpecificImage(): void {
    $command = new BatchReassignImagesCommand([
      self::IMAGE_ID_1 => ['collectionIds' => [self::COLLECTION_ID_1, self::COLLECTION_ID_2]],
    ]);

    $this->assertSame([self::COLLECTION_ID_1, self::COLLECTION_ID_2], $command->getCollectionIdsForImage(self::IMAGE_ID_1));
  }

  #[Test]
  public function itReturnsNullCollectionIdsForMissingImage(): void {
    $command = new BatchReassignImagesCommand([
      self::IMAGE_ID_1 => ['tagIds' => [self::TAG_ID_1]],
    ]);

    $this->assertNull($command->getCollectionIdsForImage(self::IMAGE_ID_2));
  }
}
