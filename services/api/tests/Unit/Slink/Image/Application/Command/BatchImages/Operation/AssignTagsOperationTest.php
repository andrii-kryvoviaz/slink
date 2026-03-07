<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Command\BatchImages\Operation;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\BatchImages\BatchImagesCommand;
use Slink\Image\Application\Command\BatchImages\Operation\AssignTagsOperation;
use Slink\Image\Domain\Image;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;
use Slink\Tag\Domain\Tag;

final class AssignTagsOperationTest extends TestCase {
  private const USER_ID = '123e4567-e89b-12d3-a456-426614174000';
  private const TAG_ID_1 = '11111111-1111-1111-1111-111111111111';
  private const TAG_ID_2 = '22222222-2222-2222-2222-222222222222';
  private const OTHER_USER_ID = '99999999-9999-9999-9999-999999999999';

  /**
   * @throws Exception
   */
  #[Test]
  public function itSupportsCommandWithTagIds(): void {
    $command = new BatchImagesCommand([], tagIds: [self::TAG_ID_1]);
    $tagRepository = $this->createStub(TagStoreRepositoryInterface::class);

    $operation = new AssignTagsOperation($tagRepository);

    $this->assertTrue($operation->supports($command));
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itDoesNotSupportCommandWithoutTagIds(): void {
    $command = new BatchImagesCommand([]);
    $tagRepository = $this->createStub(TagStoreRepositoryInterface::class);

    $operation = new AssignTagsOperation($tagRepository);

    $this->assertFalse($operation->supports($command));
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itTagsImageWithEachProvidedTag(): void {
    $command = new BatchImagesCommand([], tagIds: [self::TAG_ID_1, self::TAG_ID_2]);
    $userId = ID::fromString(self::USER_ID);

    $tag1 = $this->createStub(Tag::class);
    $tag1->method('getUserId')->willReturn($userId);

    $tag2 = $this->createStub(Tag::class);
    $tag2->method('getUserId')->willReturn($userId);

    $tagRepository = $this->createStub(TagStoreRepositoryInterface::class);
    $tagRepository->method('get')->willReturnCallback(
      fn (ID $id) => match ($id->toString()) {
        self::TAG_ID_1 => $tag1,
        self::TAG_ID_2 => $tag2,
        default => throw new \RuntimeException('Unexpected tag ID'),
      }
    );

    $image = $this->createMock(Image::class);
    $image->expects($this->exactly(2))->method('tagWith');

    $operation = new AssignTagsOperation($tagRepository);
    $operation->apply($image, $command, $userId);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itSkipsTagsNotOwnedByUser(): void {
    $command = new BatchImagesCommand([], tagIds: [self::TAG_ID_1, self::TAG_ID_2]);
    $userId = ID::fromString(self::USER_ID);
    $otherUserId = ID::fromString(self::OTHER_USER_ID);

    $ownedTag = $this->createStub(Tag::class);
    $ownedTag->method('getUserId')->willReturn($userId);

    $otherTag = $this->createStub(Tag::class);
    $otherTag->method('getUserId')->willReturn($otherUserId);

    $tagRepository = $this->createStub(TagStoreRepositoryInterface::class);
    $tagRepository->method('get')->willReturnCallback(
      fn (ID $id) => match ($id->toString()) {
        self::TAG_ID_1 => $ownedTag,
        self::TAG_ID_2 => $otherTag,
        default => throw new \RuntimeException('Unexpected tag ID'),
      }
    );

    $image = $this->createMock(Image::class);
    $image->expects($this->once())->method('tagWith');

    $operation = new AssignTagsOperation($tagRepository);
    $operation->apply($image, $command, $userId);
  }
}
