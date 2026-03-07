<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Command\BatchImages;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\BatchImages\BatchImagesCommand;

final class BatchImagesCommandTest extends TestCase {
  private const IMAGE_ID_1 = '11111111-1111-1111-1111-111111111111';
  private const IMAGE_ID_2 = '22222222-2222-2222-2222-222222222222';
  private const TAG_ID_1 = '33333333-3333-3333-3333-333333333333';
  private const COLLECTION_ID = '44444444-4444-4444-4444-444444444444';

  #[Test]
  public function itStoresAndReturnsAllProperties(): void {
    $command = new BatchImagesCommand(
      [self::IMAGE_ID_1, self::IMAGE_ID_2],
      true,
      [self::TAG_ID_1],
      self::COLLECTION_ID,
    );

    $this->assertSame([self::IMAGE_ID_1, self::IMAGE_ID_2], $command->imageIds());
    $this->assertTrue($command->isPublic());
    $this->assertSame([self::TAG_ID_1], $command->tagIds());
    $this->assertSame(self::COLLECTION_ID, $command->collectionId());
  }

  #[Test]
  public function itDefaultsNullablePropertiesToNull(): void {
    $command = new BatchImagesCommand([self::IMAGE_ID_1]);

    $this->assertSame([self::IMAGE_ID_1], $command->imageIds());
    $this->assertNull($command->isPublic());
    $this->assertNull($command->tagIds());
    $this->assertNull($command->collectionId());
  }
}
