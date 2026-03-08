<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Command\BatchReassignImages\Operation;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\BatchReassignImages\BatchReassignImagesCommand;
use Slink\Image\Application\Command\BatchReassignImages\Operation\ReassignTagsOperation;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\ValueObject\TagSet;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;
use Slink\Tag\Domain\Tag;

final class ReassignTagsOperationTest extends TestCase {
  private const USER_ID = '123e4567-e89b-12d3-a456-426614174000';
  private const IMAGE_ID = '11111111-1111-1111-1111-111111111111';
  private const TAG_ID_1 = '33333333-3333-3333-3333-333333333333';
  private const TAG_ID_2 = '44444444-4444-4444-4444-444444444444';
  private const OTHER_USER_ID = '99999999-9999-9999-9999-999999999999';

  /**
   * @throws Exception
   */
  #[Test]
  public function itSupportsCommandWithTagIds(): void {
    $command = new BatchReassignImagesCommand([
      self::IMAGE_ID => ['tagIds' => [self::TAG_ID_1]],
    ]);

    $tagRepository = $this->createStub(TagStoreRepositoryInterface::class);
    $operation = new ReassignTagsOperation($tagRepository);

    $this->assertTrue($operation->supports($command, self::IMAGE_ID));
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itDoesNotSupportCommandWithoutTagIds(): void {
    $command = new BatchReassignImagesCommand([
      self::IMAGE_ID => ['collectionIds' => ['55555555-5555-5555-5555-555555555555']],
    ]);

    $tagRepository = $this->createStub(TagStoreRepositoryInterface::class);
    $operation = new ReassignTagsOperation($tagRepository);

    $this->assertFalse($operation->supports($command, self::IMAGE_ID));
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itReassignsImageTagsWithValidTagsOnly(): void {
    $command = new BatchReassignImagesCommand([
      self::IMAGE_ID => ['tagIds' => [self::TAG_ID_1, self::TAG_ID_2]],
    ]);
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
    $image->expects($this->once())->method('reassignTags')->with(
      $this->callback(fn (TagSet $tags) => $tags->count() === 2),
      $userId,
    );

    $operation = new ReassignTagsOperation($tagRepository);
    $operation->apply($image, $command, self::IMAGE_ID, $userId);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itSkipsTagsNotOwnedByUser(): void {
    $command = new BatchReassignImagesCommand([
      self::IMAGE_ID => ['tagIds' => [self::TAG_ID_1, self::TAG_ID_2]],
    ]);
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
    $image->expects($this->once())->method('reassignTags')->with(
      $this->callback(fn (TagSet $tags) => $tags->count() === 1),
      $userId,
    );

    $operation = new ReassignTagsOperation($tagRepository);
    $operation->apply($image, $command, self::IMAGE_ID, $userId);
  }
}
