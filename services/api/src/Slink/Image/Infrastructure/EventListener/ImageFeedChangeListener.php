<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Slink\Image\Domain\Enum\ImageFeedEventType;
use Slink\Image\Infrastructure\Message\ImageFeedChanged;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: ImageView::class, entityManager: 'read_model')]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: ImageView::class, entityManager: 'read_model')]
#[AsDoctrineListener(event: Events::onFlush, connection: 'read_model')]
final readonly class ImageFeedChangeListener {
  public function __construct(
    #[Autowire(service: 'messenger.bus.feed')]
    private MessageBusInterface $messageBus,
  ) {
  }

  public function postPersist(ImageView $image, PostPersistEventArgs $args): void {
    if (!$image->getAttributes()->isPublic()) {
      return;
    }

    $this->enqueue($image->getUuid(), ImageFeedEventType::Added);
  }

  public function postUpdate(ImageView $image, PostUpdateEventArgs $args): void {
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $args->getObjectManager();
    /** @var array<string, array{0: mixed, 1: mixed}> $changeSet */
    $changeSet = $em->getUnitOfWork()->getEntityChangeSet($image);

    $visibilityChange = $this->getVisibilityChange($changeSet);
    $isPublic = $image->getAttributes()->isPublic();

    if ($visibilityChange !== null) {
      [$wasPublic] = $visibilityChange;

      if (!$wasPublic && $isPublic) {
        $this->enqueue($image->getUuid(), ImageFeedEventType::Added);
        return;
      }

      if ($wasPublic && !$isPublic) {
        $this->enqueue($image->getUuid(), ImageFeedEventType::Removed);
        return;
      }
    }

    if ($isPublic && $this->hasRelevantChanges($changeSet)) {
      $this->enqueue($image->getUuid(), ImageFeedEventType::Updated);
    }
  }

  public function onFlush(OnFlushEventArgs $args): void {
    $em = $args->getObjectManager();

    foreach ($em->getUnitOfWork()->getScheduledEntityDeletions() as $entity) {
      if ($entity instanceof ImageView && $this->claimsHide($em, $entity->getUuid())) {
        $this->enqueue($entity->getUuid(), ImageFeedEventType::Removed);
      }
    }
  }

  private function claimsHide(EntityManagerInterface $em, string $imageId): bool {
    $connection = $em->getConnection();

    $claimed = $connection->update(
      $connection->quoteIdentifier('image'),
      ['is_public' => false],
      ['uuid' => $imageId, 'is_public' => true],
      ['is_public' => ParameterType::BOOLEAN],
    );

    return (int) $claimed > 0;
  }

  /**
   * @param array<string, array{0: mixed, 1: mixed}> $changeSet
   * @return array{0: bool, 1: bool}|null
   */
  private function getVisibilityChange(array $changeSet): ?array {
    if (isset($changeSet['attributes.isPublic'])) {
      return [(bool) $changeSet['attributes.isPublic'][0], (bool) $changeSet['attributes.isPublic'][1]];
    }
    return null;
  }

  /**
   * @param array<string, array{0: mixed, 1: mixed}> $changeSet
   */
  private function hasRelevantChanges(array $changeSet): bool {
    $relevantKeys = ['attributes.description', 'attributes.fileName', 'bookmarkCount'];

    foreach ($relevantKeys as $key) {
      if (isset($changeSet[$key])) {
        return true;
      }
    }

    return false;
  }

  private function enqueue(string $imageId, ImageFeedEventType $eventType): void {
    $this->messageBus->dispatch(new ImageFeedChanged($imageId, $eventType));
  }
}
