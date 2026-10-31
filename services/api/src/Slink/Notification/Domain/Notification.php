<?php

declare(strict_types=1);

namespace Slink\Notification\Domain;

use Slink\Notification\Domain\Enum\NotificationType;
use Slink\Notification\Domain\Event\NotificationWasCreated;
use Slink\Notification\Domain\Event\NotificationWasRead;
use Slink\Notification\Infrastructure\ReadModel\View\NotificationView;
use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final class Notification extends AbstractAggregateRoot {
  private ID $userId;
  private NotificationType $type;
  private ID $referenceId;
  private ?ID $relatedCommentId = null;
  private ?ID $actorId = null;
  private DateTime $createdAt;
  private ?DateTime $readAt = null;

  public static function create(
    ID $id,
    ID $userId,
    NotificationType $type,
    ID $referenceId,
    ?ID $relatedCommentId = null,
    ?ID $actorId = null,
  ): self {
    $notification = new self($id);
    $now = DateTime::now();

    $notification->recordThat(new NotificationWasCreated(
      $id,
      $userId,
      $type,
      $referenceId,
      $relatedCommentId,
      $actorId,
      $now,
    ));

    return $notification;
  }

  public static function fromView(NotificationView $view): self {
    $notification = new self(ID::fromString($view->getId()));
    $notification->userId = ID::fromString($view->getUserId());
    $notification->type = $view->getType();
    $notification->referenceId = ID::fromString($view->getReference()->getUuid());
    $notification->relatedCommentId = ID::fromUnknown($view->getRelatedComment()?->getId());
    $notification->actorId = ID::fromUnknown($view->getActor()?->getUuid());
    $notification->createdAt = $view->getCreatedAt();
    $notification->readAt = $view->getReadAt();

    return $notification;
  }

  public function markAsRead(): void {
    if ($this->isRead()) {
      return;
    }

    $this->recordThat(new NotificationWasRead(
      $this->aggregateRootId(),
      DateTime::now(),
    ));
  }

  public function applyNotificationWasCreated(NotificationWasCreated $event): void {
    $this->userId = $event->userId;
    $this->type = $event->type;
    $this->referenceId = $event->referenceId;
    $this->relatedCommentId = $event->relatedCommentId;
    $this->actorId = $event->actorId;
    $this->createdAt = $event->createdAt;
  }

  public function applyNotificationWasRead(NotificationWasRead $event): void {
    $this->readAt = $event->readAt;
  }

  public function getUserId(): ID {
    return $this->userId;
  }

  public function getType(): NotificationType {
    return $this->type;
  }

  public function getReferenceId(): ID {
    return $this->referenceId;
  }

  public function getRelatedCommentId(): ?ID {
    return $this->relatedCommentId;
  }

  public function getActorId(): ?ID {
    return $this->actorId;
  }

  public function getCreatedAt(): DateTime {
    return $this->createdAt;
  }

  public function getReadAt(): ?DateTime {
    return $this->readAt;
  }

  public function isRead(): bool {
    return $this->readAt !== null;
  }
}
