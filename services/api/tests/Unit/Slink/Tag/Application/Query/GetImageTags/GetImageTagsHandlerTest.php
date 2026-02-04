<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Application\Query\GetImageTags;

use Doctrine\ORM\NonUniqueResultException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Tag\Application\Query\GetImageTags\GetImageTagsHandler;
use Slink\Tag\Application\Query\GetImageTags\GetImageTagsQuery;
use Slink\Tag\Domain\Exception\TagAccessDeniedException;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class GetImageTagsHandlerTest extends TestCase {

  #[Test]
  public function itReturnsTagsForOwnedImage(): void {
    $tagRepository = $this->createMock(TagRepositoryInterface::class);
    $imageRepository = $this->createMock(ImageRepositoryInterface::class);
    $imageView = $this->createStub(ImageView::class);
    $userView = $this->createStub(UserView::class);
    $tagView1 = $this->createStub(TagView::class);
    $tagView2 = $this->createStub(TagView::class);
    
    $imageId = 'image-123';
    $userId = 'user-456';
    $userUuid = 'user-456';
    
    $userView->method('getUuid')->willReturn($userUuid);
    $imageView->method('getUser')->willReturn($userView);
    
    $imageRepository->expects($this->once())
      ->method('oneById')
      ->with($imageId)
      ->willReturn($imageView);

    $tagRepository->expects($this->once())
      ->method('findByImageId')
      ->with($this->callback(fn($id) => $id->toString() === $imageId))
      ->willReturn([$tagView1, $tagView2]);

    $handler = new GetImageTagsHandler($tagRepository, $imageRepository);
    $query = new GetImageTagsQuery($imageId);

    $result = $handler($query, $userId);

    $this->assertInstanceOf(Collection::class, $result);
    $this->assertEquals(1, $result->page);
    $this->assertEquals(2, $result->total);
    $this->assertEquals(2, $result->limit);
    $this->assertCount(2, $result->data);
    $dataArray = is_array($result->data) ? $result->data : iterator_to_array($result->data);
    $this->assertInstanceOf(Item::class, $dataArray[0]);
    $this->assertInstanceOf(Item::class, $dataArray[1]);
  }

  #[Test]
  public function itThrowsAccessDeniedWhenUserDoesNotOwnImage(): void {
    $tagRepository = $this->createMock(TagRepositoryInterface::class);
    $imageRepository = $this->createMock(ImageRepositoryInterface::class);
    $imageView = $this->createStub(ImageView::class);
    $userView = $this->createStub(UserView::class);
    
    $imageId = 'image-123';
    $userId = 'user-456';
    $ownerUuid = 'different-user-789';
    
    $userView->method('getUuid')->willReturn($ownerUuid);
    $imageView->method('getUser')->willReturn($userView);
    
    $imageRepository->expects($this->once())
      ->method('oneById')
      ->with($imageId)
      ->willReturn($imageView);

    $tagRepository->expects($this->never())
      ->method('findByImageId');

    $handler = new GetImageTagsHandler($tagRepository, $imageRepository);
    $query = new GetImageTagsQuery($imageId);

    $this->expectException(TagAccessDeniedException::class);
    $this->expectExceptionMessage('You can only access your own tags');

    $handler($query, $userId);
  }

  #[Test]
  public function itThrowsAccessDeniedWhenImageHasNoOwner(): void {
    $tagRepository = $this->createMock(TagRepositoryInterface::class);
    $imageRepository = $this->createMock(ImageRepositoryInterface::class);
    $imageView = $this->createStub(ImageView::class);
    
    $imageId = 'image-123';
    $userId = 'user-456';
    
    $imageView->method('getUser')->willReturn(null);
    
    $imageRepository->expects($this->once())
      ->method('oneById')
      ->with($imageId)
      ->willReturn($imageView);

    $tagRepository->expects($this->never())
      ->method('findByImageId');

    $handler = new GetImageTagsHandler($tagRepository, $imageRepository);
    $query = new GetImageTagsQuery($imageId);

    $this->expectException(TagAccessDeniedException::class);
    $this->expectExceptionMessage('You can only access your own tags');

    $handler($query, $userId);
  }

  #[Test]
  public function itReturnsEmptyCollectionWhenImageHasNoTags(): void {
    $tagRepository = $this->createMock(TagRepositoryInterface::class);
    $imageRepository = $this->createMock(ImageRepositoryInterface::class);
    $imageView = $this->createStub(ImageView::class);
    $userView = $this->createStub(UserView::class);
    
    $imageId = 'image-empty';
    $userId = 'user-owner';
    $userUuid = 'user-owner';
    
    $userView->method('getUuid')->willReturn($userUuid);
    $imageView->method('getUser')->willReturn($userView);
    
    $imageRepository->expects($this->once())
      ->method('oneById')
      ->with($imageId)
      ->willReturn($imageView);

    $tagRepository->expects($this->once())
      ->method('findByImageId')
      ->with($this->callback(fn($id) => $id->toString() === $imageId))
      ->willReturn([]);

    $handler = new GetImageTagsHandler($tagRepository, $imageRepository);
    $query = new GetImageTagsQuery($imageId);

    $result = $handler($query, $userId);

    $this->assertInstanceOf(Collection::class, $result);
    $this->assertEquals(1, $result->page);
    $this->assertEquals(0, $result->total);
    $this->assertEquals(0, $result->limit);
    $this->assertEmpty($result->data);
  }

  #[Test]
  public function itThrowsNotFoundExceptionWhenImageDoesNotExist(): void {
    $tagRepository = $this->createMock(TagRepositoryInterface::class);
    $imageRepository = $this->createMock(ImageRepositoryInterface::class);
    
    $imageId = 'non-existent-image';
    $userId = 'user-123';
    
    $imageRepository->expects($this->once())
      ->method('oneById')
      ->with($imageId)
      ->willThrowException(new NotFoundException());

    $tagRepository->expects($this->never())
      ->method('findByImageId');

    $handler = new GetImageTagsHandler($tagRepository, $imageRepository);
    $query = new GetImageTagsQuery($imageId);

    $this->expectException(NotFoundException::class);

    $handler($query, $userId);
  }

  #[Test]
  public function itThrowsNonUniqueResultExceptionWhenRepositoryFails(): void {
    $tagRepository = $this->createMock(TagRepositoryInterface::class);
    $imageRepository = $this->createMock(ImageRepositoryInterface::class);
    
    $imageId = 'duplicate-image';
    $userId = 'user-123';
    
    $imageRepository->expects($this->once())
      ->method('oneById')
      ->with($imageId)
      ->willThrowException(new NonUniqueResultException());

    $tagRepository->expects($this->never())
      ->method('findByImageId');

    $handler = new GetImageTagsHandler($tagRepository, $imageRepository);
    $query = new GetImageTagsQuery($imageId);

    $this->expectException(NonUniqueResultException::class);

    $handler($query, $userId);
  }

  #[Test]
  public function itConvertsTagsToItemsCorrectly(): void {
    $tagRepository = $this->createStub(TagRepositoryInterface::class);
    $imageRepository = $this->createStub(ImageRepositoryInterface::class);
    $imageView = $this->createStub(ImageView::class);
    $userView = $this->createStub(UserView::class);
    $tagView = $this->createStub(TagView::class);

    $imageId = 'image-items';
    $userId = 'user-items';
    $userUuid = 'user-items';

    $userView->method('getUuid')->willReturn($userUuid);
    $imageView->method('getUser')->willReturn($userView);

    $imageRepository->method('oneById')->willReturn($imageView);
    $tagRepository->method('findByImageId')->willReturn([$tagView]);

    $handler = new GetImageTagsHandler($tagRepository, $imageRepository);
    $query = new GetImageTagsQuery($imageId);

    $result = $handler($query, $userId);

    $this->assertCount(1, $result->data);
    $dataArray = is_array($result->data) ? $result->data : iterator_to_array($result->data);
    $this->assertInstanceOf(Item::class, $dataArray[0]);
  }

  #[Test]
  public function itValidatesImageIdIsConvertedToIdObject(): void {
    $tagRepository = $this->createMock(TagRepositoryInterface::class);
    $imageRepository = $this->createMock(ImageRepositoryInterface::class);
    $imageView = $this->createStub(ImageView::class);
    $userView = $this->createStub(UserView::class);
    
    $imageId = '550e8400-e29b-41d4-a716-446655440000';
    $userId = '660e8400-e29b-41d4-a716-446655440000';
    $userUuid = '660e8400-e29b-41d4-a716-446655440000';
    
    $userView->method('getUuid')->willReturn($userUuid);
    $imageView->method('getUser')->willReturn($userView);
    
    $imageRepository->expects($this->once())
      ->method('oneById')
      ->with($imageId)
      ->willReturn($imageView);

    $tagRepository->expects($this->once())
      ->method('findByImageId')
      ->with($this->callback(function($id) use ($imageId) {
        return $id->toString() === $imageId;
      }))
      ->willReturn([]);

    $handler = new GetImageTagsHandler($tagRepository, $imageRepository);
    $query = new GetImageTagsQuery($imageId);

    $result = $handler($query, $userId);

    $this->assertInstanceOf(Collection::class, $result);
  }

  #[Test]
  public function itHandlesLargeNumberOfTags(): void {
    $tagRepository = $this->createStub(TagRepositoryInterface::class);
    $imageRepository = $this->createStub(ImageRepositoryInterface::class);
    $imageView = $this->createStub(ImageView::class);
    $userView = $this->createStub(UserView::class);

    $imageId = 'image-many-tags';
    $userId = 'user-many';
    $userUuid = 'user-many';

    $tags = [];
    for ($i = 0; $i < 100; $i++) {
      $tags[] = $this->createStub(TagView::class);
    }

    $userView->method('getUuid')->willReturn($userUuid);
    $imageView->method('getUser')->willReturn($userView);

    $imageRepository->method('oneById')->willReturn($imageView);
    $tagRepository->method('findByImageId')->willReturn($tags);

    $handler = new GetImageTagsHandler($tagRepository, $imageRepository);
    $query = new GetImageTagsQuery($imageId);

    $result = $handler($query, $userId);

    $this->assertInstanceOf(Collection::class, $result);
    $this->assertEquals(100, $result->total);
    $this->assertEquals(100, $result->limit);
    $this->assertCount(100, $result->data);
  }
}