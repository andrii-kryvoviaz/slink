<?php

declare(strict_types=1);

namespace Slink\Comment\Domain;

use Slink\Comment\Domain\Event\CommentWasCreated;
use Slink\Comment\Domain\Event\CommentWasDeleted;
use Slink\Comment\Domain\Event\CommentWasEdited;
use Slink\Comment\Domain\Exception\CommentEditWindowExpiredException;
use Slink\Comment\Domain\Service\CommentEditPolicy;
use Slink\Comment\Domain\ValueObject\CommentContent;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final class Comment extends AbstractAggregateRoot {
  private ID $imageId;
  private ID $userId;
  private ?ID $referencedCommentId = null;
  private CommentContent $content;
  private DateTime $createdAt;
  private ?DateTime $updatedAt = null;
  private ?DateTime $deletedAt = null;

  public static function create(
    ID $id,
    ID $imageId,
    ID $userId,
    ?ID $referencedCommentId,
    CommentContent $content,
  ): self {
    $comment = new self($id);
    $now = DateTime::now();

    $comment->recordThat(new CommentWasCreated(
      $id,
      $imageId,
      $userId,
      $referencedCommentId,
      $content,
      $now,
    ));

    return $comment;
  }

  public static function fromView(CommentView $view): self {
    $comment = new self(ID::fromString($view->getId()));
    $comment->imageId = ID::fromString($view->getImageId());
    $comment->userId = ID::fromString($view->getAuthorId());
    $comment->referencedCommentId = ID::fromUnknown($view->getReferencedComment()?->getId());
    $comment->content = CommentContent::fromStored($view->getContent());
    $comment->createdAt = $view->getCreatedAt();
    $comment->updatedAt = $view->getUpdatedAt();
    $comment->deletedAt = $view->getDeletedAt();

    return $comment;
  }

  public function edit(CommentContent $content): void {
    if ($this->isDeleted()) {
      throw new \DomainException('Cannot edit a deleted comment');
    }

    if (!CommentEditPolicy::canEdit($this->createdAt)) {
      throw new CommentEditWindowExpiredException();
    }

    $this->recordThat(new CommentWasEdited(
      $this->aggregateRootId(),
      $content,
      DateTime::now(),
    ));
  }

  public function delete(): void {
    if ($this->isDeleted()) {
      return;
    }

    $this->recordThat(new CommentWasDeleted(
      $this->aggregateRootId(),
      DateTime::now(),
    ));
  }

  public function applyCommentWasCreated(CommentWasCreated $event): void {
    $this->imageId = $event->imageId;
    $this->userId = $event->userId;
    $this->referencedCommentId = $event->referencedCommentId;
    $this->content = $event->content;
    $this->createdAt = $event->createdAt;
  }

  public function applyCommentWasEdited(CommentWasEdited $event): void {
    $this->content = $event->content;
    $this->updatedAt = $event->updatedAt;
  }

  public function applyCommentWasDeleted(CommentWasDeleted $event): void {
    $this->deletedAt = $event->deletedAt;
  }

  public function getImageId(): ID {
    return $this->imageId;
  }

  public function getUserId(): ID {
    return $this->userId;
  }

  public function getReferencedCommentId(): ?ID {
    return $this->referencedCommentId;
  }

  public function getContent(): CommentContent {
    return $this->content;
  }

  public function getCreatedAt(): DateTime {
    return $this->createdAt;
  }

  public function getUpdatedAt(): ?DateTime {
    return $this->updatedAt;
  }

  public function getDeletedAt(): ?DateTime {
    return $this->deletedAt;
  }

  public function isDeleted(): bool {
    return $this->deletedAt !== null;
  }

  public function isOwnedBy(ID $userId): bool {
    return $this->userId->equals($userId);
  }
}
