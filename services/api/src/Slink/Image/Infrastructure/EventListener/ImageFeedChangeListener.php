<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Slink\Image\Domain\Enum\ImageFeedEventType;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\Service\ServerSentEventPublisherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: ImageView::class, entityManager: 'read_model')]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: ImageView::class, entityManager: 'read_model')]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: ImageView::class, entityManager: 'read_model')]
final readonly class ImageFeedChangeListener {
  private const TOPIC = 'public-feed';

  public function __construct(
    private ServerSentEventPublisherInterface $publisher,
    private NormalizerInterface $serializer,
  ) {
  }

  public function postPersist(ImageView $image, PostPersistEventArgs $args): void {
    if (!$image->getAttributes()->isPublic()) {
      return;
    }

    $this->publish($image, ImageFeedEventType::Added);
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
        $this->publish($image, ImageFeedEventType::Added);
        return;
      }

      if ($wasPublic && !$isPublic) {
        $this->publishRemoval($image->getUuid());
        return;
      }
    }

    if ($isPublic && $this->hasRelevantChanges($changeSet)) {
      $this->publish($image, ImageFeedEventType::Updated);
    }
  }

  public function postRemove(ImageView $image, PostRemoveEventArgs $args): void {
    if (!$image->getAttributes()->isPublic()) {
      return;
    }

    $this->publishRemoval($image->getUuid());
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

  private function publish(ImageView $image, ImageFeedEventType $eventType): void {
    /** @var array<string, mixed> $normalized */
    $normalized = $this->serializer->normalize($image, context: ['groups' => ['public']]);

    $this->publisher->publish(self::TOPIC, [
      'event' => $eventType->value,
      'image' => $normalized,
    ]);
  }

  private function publishRemoval(string $imageId): void {
    $this->publisher->publish(self::TOPIC, [
      'event' => ImageFeedEventType::Removed->value,
      'imageId' => $imageId,
    ]);
  }
}
