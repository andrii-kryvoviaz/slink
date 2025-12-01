<?php

declare(strict_types=1);

namespace Slink\Comment\Domain;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use Slink\Comment\Domain\Event\CommentWasCreated;
use Slink\Comment\Domain\Event\CommentWasDeleted;
use Slink\Comment\Domain\Event\CommentWasEdited;
use Slink\Comment\Domain\Exception\CommentEditWindowExpiredException;
use Slink\Comment\Domain\Service\CommentEditPolicy;
use Slink\Comment\Domain\ValueObject\CommentContent;
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

  protected function createSnapshotState(): array {
    return [
      'imageId' => $this->imageId->toString(),
      'userId' => $this->userId->toString(),
      'referencedCommentId' => $this->referencedCommentId?->toString(),
      'content' => $this->content->toString(),
      'createdAt' => $this->createdAt->toString(),
      'updatedAt' => $this->updatedAt?->toString(),
      'deletedAt' => $this->deletedAt?->toString(),
    ];
  }

  protected static function reconstituteFromSnapshotState(AggregateRootId $id, $state): AggregateRootWithSnapshotting {
    $comment = new self(ID::fromString($id->toString()));
    $comment->imageId = ID::fromString($state['imageId']);
    $comment->userId = ID::fromString($state['userId']);
    $comment->referencedCommentId = isset($state['referencedCommentId'])
      ? ID::fromString($state['referencedCommentId'])
      : null;
    $comment->content = CommentContent::fromString($state['content']);
    $comment->createdAt = DateTime::fromString($state['createdAt']);
    $comment->updatedAt = isset($state['updatedAt'])
      ? DateTime::fromString($state['updatedAt'])
      : null;
    $comment->deletedAt = isset($state['deletedAt'])
      ? DateTime::fromString($state['deletedAt'])
      : null;

    return $comment;
  }
}
