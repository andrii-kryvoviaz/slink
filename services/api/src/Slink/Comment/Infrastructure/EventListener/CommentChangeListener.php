<?php

declare(strict_types=1);

namespace Slink\Comment\Infrastructure\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Slink\Comment\Domain\Enum\CommentEventType;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Slink\Shared\Domain\Service\ServerSentEventPublisherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: CommentView::class, entityManager: 'read_model')]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: CommentView::class, entityManager: 'read_model')]
final class CommentChangeListener {
  public function __construct(
    private readonly ServerSentEventPublisherInterface $publisher,
    private readonly NormalizerInterface $serializer,
  ) {
  }

  public function postPersist(CommentView $comment, PostPersistEventArgs $args): void {
    $this->publish($comment, CommentEventType::Created);
  }

  public function postUpdate(CommentView $comment, PostUpdateEventArgs $args): void {
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $args->getObjectManager();
    $changeSet = $em->getUnitOfWork()->getEntityChangeSet($comment);

    $eventType = isset($changeSet['deletedAt']) && $changeSet['deletedAt'][1] !== null
      ? CommentEventType::Deleted
      : CommentEventType::Edited;

    $this->publish($comment, $eventType);
  }

  private function publish(CommentView $comment, CommentEventType $eventType): void {
    $topic = "comments/image/{$comment->getImageId()}";

    if ($eventType === CommentEventType::Deleted) {
      $this->publisher->publish($topic, [
        'event' => $eventType->value,
        'commentId' => $comment->getId(),
      ]);
      return;
    }

    /** @var array<string, mixed> $normalized */
    $normalized = $this->serializer->normalize($comment, context: ['groups' => ['public']]);

    $this->publisher->publish($topic, [
      'event' => $eventType->value,
      'comment' => $normalized,
    ]);
  }
}
