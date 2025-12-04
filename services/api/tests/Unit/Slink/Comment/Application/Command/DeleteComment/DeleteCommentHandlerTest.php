<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Comment\Application\Command\DeleteComment;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Comment\Application\Command\DeleteComment\DeleteCommentCommand;
use Slink\Comment\Application\Command\DeleteComment\DeleteCommentHandler;
use Slink\Comment\Domain\Comment;
use Slink\Comment\Domain\Repository\CommentRepositoryInterface;
use Slink\Comment\Domain\Repository\CommentStoreRepositoryInterface;
use Slink\Comment\Domain\ValueObject\CommentContent;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\Exception\ForbiddenException;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class DeleteCommentHandlerTest extends TestCase {
  #[Test]
  public function itDeletesCommentByOwner(): void {
    $commentId = ID::generate();
    $userId = ID::generate();
    $imageId = ID::generate();

    $commentStore = $this->createMock(CommentStoreRepositoryInterface::class);
    $commentRepository = $this->createMock(CommentRepositoryInterface::class);
    $imageRepository = $this->createMock(ImageRepositoryInterface::class);

    $comment = Comment::create(
      $commentId,
      $imageId,
      $userId,
      null,
      CommentContent::fromString('Test comment'),
    );

    $commentView = $this->createMock(CommentView::class);
    $commentView->method('getImageId')->willReturn($imageId->toString());

    $differentUser = $this->createMock(UserView::class);
    $differentUser->method('getUuid')->willReturn(ID::generate()->toString());

    $imageView = $this->createMock(ImageView::class);
    $imageView->method('getUser')->willReturn($differentUser);

    $commentStore->expects($this->once())
      ->method('get')
      ->willReturn($comment);

    $commentRepository->expects($this->once())
      ->method('oneById')
      ->willReturn($commentView);

    $imageRepository->expects($this->once())
      ->method('oneById')
      ->willReturn($imageView);

    $commentStore->expects($this->once())
      ->method('store')
      ->with($comment);

    $handler = new DeleteCommentHandler($commentStore, $commentRepository, $imageRepository);

    $command = new DeleteCommentCommand();

    $handler($command, $commentId->toString(), $userId->toString());

    $this->assertTrue($comment->isDeleted());
  }

  #[Test]
  public function itDeletesCommentByImageOwner(): void {
    $commentId = ID::generate();
    $commentOwnerId = ID::generate();
    $imageOwnerId = ID::generate();
    $imageId = ID::generate();

    $commentStore = $this->createMock(CommentStoreRepositoryInterface::class);
    $commentRepository = $this->createMock(CommentRepositoryInterface::class);
    $imageRepository = $this->createMock(ImageRepositoryInterface::class);

    $comment = Comment::create(
      $commentId,
      $imageId,
      $commentOwnerId,
      null,
      CommentContent::fromString('Test comment'),
    );

    $commentView = $this->createMock(CommentView::class);
    $commentView->method('getImageId')->willReturn($imageId->toString());

    $imageOwner = $this->createMock(UserView::class);
    $imageOwner->method('getUuid')->willReturn($imageOwnerId->toString());

    $imageView = $this->createMock(ImageView::class);
    $imageView->method('getUser')->willReturn($imageOwner);

    $commentStore->method('get')->willReturn($comment);
    $commentRepository->method('oneById')->willReturn($commentView);
    $imageRepository->method('oneById')->willReturn($imageView);

    $commentStore->expects($this->once())
      ->method('store')
      ->with($comment);

    $handler = new DeleteCommentHandler($commentStore, $commentRepository, $imageRepository);

    $command = new DeleteCommentCommand();

    $handler($command, $commentId->toString(), $imageOwnerId->toString());

    $this->assertTrue($comment->isDeleted());
  }

  #[Test]
  public function itThrowsForbiddenExceptionWhenUserIsNotOwnerOrImageOwner(): void {
    $this->expectException(ForbiddenException::class);
    $this->expectExceptionMessage('You can only delete your own comments or comments on your images');

    $commentId = ID::generate();
    $commentOwnerId = ID::generate();
    $imageOwnerId = ID::generate();
    $randomUserId = ID::generate();
    $imageId = ID::generate();

    $commentStore = $this->createMock(CommentStoreRepositoryInterface::class);
    $commentRepository = $this->createMock(CommentRepositoryInterface::class);
    $imageRepository = $this->createMock(ImageRepositoryInterface::class);

    $comment = Comment::create(
      $commentId,
      $imageId,
      $commentOwnerId,
      null,
      CommentContent::fromString('Test comment'),
    );

    $commentView = $this->createMock(CommentView::class);
    $commentView->method('getImageId')->willReturn($imageId->toString());

    $imageOwner = $this->createMock(UserView::class);
    $imageOwner->method('getUuid')->willReturn($imageOwnerId->toString());

    $imageView = $this->createMock(ImageView::class);
    $imageView->method('getUser')->willReturn($imageOwner);

    $commentStore->method('get')->willReturn($comment);
    $commentRepository->method('oneById')->willReturn($commentView);
    $imageRepository->method('oneById')->willReturn($imageView);

    $handler = new DeleteCommentHandler($commentStore, $commentRepository, $imageRepository);

    $command = new DeleteCommentCommand();

    $handler($command, $commentId->toString(), $randomUserId->toString());
  }

  #[Test]
  public function itDoesNotThrowWhenDeletingAlreadyDeletedComment(): void {
    $commentId = ID::generate();
    $userId = ID::generate();
    $imageId = ID::generate();

    $commentStore = $this->createMock(CommentStoreRepositoryInterface::class);
    $commentRepository = $this->createMock(CommentRepositoryInterface::class);
    $imageRepository = $this->createMock(ImageRepositoryInterface::class);

    $comment = Comment::create(
      $commentId,
      $imageId,
      $userId,
      null,
      CommentContent::fromString('Test comment'),
    );
    $comment->delete();

    $commentView = $this->createMock(CommentView::class);
    $commentView->method('getImageId')->willReturn($imageId->toString());

    $imageOwner = $this->createMock(UserView::class);
    $imageOwner->method('getUuid')->willReturn(ID::generate()->toString());

    $imageView = $this->createMock(ImageView::class);
    $imageView->method('getUser')->willReturn($imageOwner);

    $commentStore->method('get')->willReturn($comment);
    $commentRepository->method('oneById')->willReturn($commentView);
    $imageRepository->method('oneById')->willReturn($imageView);

    $commentStore->expects($this->once())->method('store');

    $handler = new DeleteCommentHandler($commentStore, $commentRepository, $imageRepository);

    $command = new DeleteCommentCommand();

    $handler($command, $commentId->toString(), $userId->toString());
  }

  #[Test]
  public function itHandlesImageWithNullUser(): void {
    $commentId = ID::generate();
    $userId = ID::generate();
    $imageId = ID::generate();

    $commentStore = $this->createMock(CommentStoreRepositoryInterface::class);
    $commentRepository = $this->createMock(CommentRepositoryInterface::class);
    $imageRepository = $this->createMock(ImageRepositoryInterface::class);

    $comment = Comment::create(
      $commentId,
      $imageId,
      $userId,
      null,
      CommentContent::fromString('Test comment'),
    );

    $commentView = $this->createMock(CommentView::class);
    $commentView->method('getImageId')->willReturn($imageId->toString());

    $imageView = $this->createMock(ImageView::class);
    $imageView->method('getUser')->willReturn(null);

    $commentStore->method('get')->willReturn($comment);
    $commentRepository->method('oneById')->willReturn($commentView);
    $imageRepository->method('oneById')->willReturn($imageView);

    $commentStore->expects($this->once())->method('store');

    $handler = new DeleteCommentHandler($commentStore, $commentRepository, $imageRepository);

    $command = new DeleteCommentCommand();

    $handler($command, $commentId->toString(), $userId->toString());

    $this->assertTrue($comment->isDeleted());
  }
}
