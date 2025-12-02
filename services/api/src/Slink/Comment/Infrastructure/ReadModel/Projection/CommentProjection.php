<?php

declare(strict_types=1);

namespace Slink\Comment\Infrastructure\ReadModel\Projection;

use Doctrine\ORM\EntityManagerInterface;
use Slink\Comment\Domain\Event\CommentWasCreated;
use Slink\Comment\Domain\Event\CommentWasDeleted;
use Slink\Comment\Domain\Event\CommentWasEdited;
use Slink\Comment\Domain\Repository\CommentRepositoryInterface;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class CommentProjection extends AbstractProjection {
  public function __construct(
    private readonly CommentRepositoryInterface $repository,
    private readonly EntityManagerInterface $em,
  ) {
  }

  public function handleCommentWasCreated(CommentWasCreated $event): void {
    /** @var ImageView $image */
    $image = $this->em->getReference(ImageView::class, $event->imageId->toString());
    /** @var UserView $user */
    $user = $this->em->getReference(UserView::class, $event->userId->toString());

    $comment = new CommentView(
      $event->id->toString(),
      $image,
      $user,
      $event->content->toString(),
      $event->createdAt,
    );

    if ($event->referencedCommentId !== null) {
      $referencedComment = $this->repository->oneById($event->referencedCommentId->toString());
      $comment->setReferencedComment($referencedComment);
    }

    $this->repository->add($comment);
  }

  public function handleCommentWasEdited(CommentWasEdited $event): void {
    $comment = $this->repository->oneById($event->id->toString());

    $comment->updateContent($event->content->toString(), $event->updatedAt);
  }

  public function handleCommentWasDeleted(CommentWasDeleted $event): void {
    $comment = $this->repository->oneById($event->id->toString());

    $comment->markAsDeleted($event->deletedAt);
  }
}
