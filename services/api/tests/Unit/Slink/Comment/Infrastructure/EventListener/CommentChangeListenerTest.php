<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Comment\Infrastructure\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Comment\Domain\Enum\CommentEventType;
use Slink\Comment\Infrastructure\EventListener\CommentChangeListener;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Slink\Shared\Domain\Service\ServerSentEventPublisherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class CommentChangeListenerTest extends TestCase {
  private const array NORMALIZED_COMMENT = ['id' => 'comment-1', 'content' => 'Hello'];

  #[Test]
  #[Group('sc-887')]
  public function itPublishesPrivateUpdateOnPersist(): void {
    $comment = $this->createCommentStub();

    $publisher = $this->createMock(ServerSentEventPublisherInterface::class);
    $publisher->expects($this->once())
      ->method('publish')
      ->with(
        'comments/image/image-123',
        [
          'event' => CommentEventType::Created->value,
          'comment' => self::NORMALIZED_COMMENT,
        ],
        true,
      );

    $listener = new CommentChangeListener($publisher, $this->createPublicGroupNormalizer($comment));
    $listener->postPersist($comment, new PostPersistEventArgs($comment, $this->createStub(EntityManagerInterface::class)));
  }

  #[Test]
  #[Group('sc-887')]
  public function itPublishesPrivateUpdateOnEdit(): void {
    $comment = $this->createCommentStub();

    $publisher = $this->createMock(ServerSentEventPublisherInterface::class);
    $publisher->expects($this->once())
      ->method('publish')
      ->with(
        'comments/image/image-123',
        [
          'event' => CommentEventType::Edited->value,
          'comment' => self::NORMALIZED_COMMENT,
        ],
        true,
      );

    $listener = new CommentChangeListener($publisher, $this->createPublicGroupNormalizer($comment));
    $listener->postUpdate($comment, $this->createPostUpdateEventArgs($comment, ['content' => ['old', 'new']]));
  }

  #[Test]
  #[Group('sc-887')]
  public function itPublishesPrivateUpdateOnDelete(): void {
    $comment = $this->createCommentStub();

    $publisher = $this->createMock(ServerSentEventPublisherInterface::class);
    $publisher->expects($this->once())
      ->method('publish')
      ->with(
        'comments/image/image-123',
        [
          'event' => CommentEventType::Deleted->value,
          'commentId' => 'comment-1',
        ],
        true,
      );

    $normalizer = $this->createMock(NormalizerInterface::class);
    $normalizer->expects($this->never())->method('normalize');

    $listener = new CommentChangeListener($publisher, $normalizer);
    $listener->postUpdate($comment, $this->createPostUpdateEventArgs($comment, ['deletedAt' => [null, new \DateTimeImmutable()]]));
  }

  private function createCommentStub(): CommentView {
    $comment = $this->createStub(CommentView::class);
    $comment->method('getImageId')->willReturn('image-123');
    $comment->method('getId')->willReturn('comment-1');

    return $comment;
  }

  private function createPublicGroupNormalizer(CommentView $comment): NormalizerInterface {
    $normalizer = $this->createMock(NormalizerInterface::class);
    $normalizer->expects($this->once())
      ->method('normalize')
      ->with($this->identicalTo($comment), $this->anything(), ['groups' => ['public']])
      ->willReturn(self::NORMALIZED_COMMENT);

    return $normalizer;
  }

  /**
   * @param array<string, array{mixed, mixed}> $changeSet
   */
  private function createPostUpdateEventArgs(CommentView $comment, array $changeSet): PostUpdateEventArgs {
    $unitOfWork = $this->createStub(UnitOfWork::class);
    $unitOfWork->method('getEntityChangeSet')->willReturn($changeSet);

    $entityManager = $this->createStub(EntityManagerInterface::class);
    $entityManager->method('getUnitOfWork')->willReturn($unitOfWork);

    return new PostUpdateEventArgs($comment, $entityManager);
  }
}
