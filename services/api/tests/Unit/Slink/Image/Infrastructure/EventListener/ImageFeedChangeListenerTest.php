<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Infrastructure\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Infrastructure\EventListener\ImageFeedChangeListener;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\Service\ServerSentEventPublisherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Group('sc-886')]
final class ImageFeedChangeListenerTest extends TestCase {
  private const TOPIC = 'public-feed';
  private const UUID = 'image-uuid-1';
  private const NORMALIZED = ['id' => self::UUID, 'fileName' => 'normalized.png'];

  private function imageView(bool $isPublic): ImageView {
    $image = $this->createStub(ImageView::class);
    $image->method('getAttributes')->willReturn(ImageAttributes::create('photo.png', 'a photo', $isPublic));
    $image->method('getUuid')->willReturn(self::UUID);

    return $image;
  }

  private function normalizer(?ImageView $expected = null): NormalizerInterface {
    if ($expected === null) {
      $normalizer = $this->createStub(NormalizerInterface::class);
      $normalizer->method('normalize')->willReturn(self::NORMALIZED);

      return $normalizer;
    }

    $normalizer = $this->createMock(NormalizerInterface::class);
    $normalizer->expects($this->once())
      ->method('normalize')
      ->with($expected, null, ['groups' => ['public']])
      ->willReturn(self::NORMALIZED);

    return $normalizer;
  }

  private function publisherExpectingNothing(): MockObject&ServerSentEventPublisherInterface {
    $publisher = $this->createMock(ServerSentEventPublisherInterface::class);
    $publisher->expects($this->never())->method('publish');

    return $publisher;
  }

  /**
   * @param array<string, mixed> $data
   */
  private function publisherExpecting(array $data): MockObject&ServerSentEventPublisherInterface {
    $publisher = $this->createMock(ServerSentEventPublisherInterface::class);
    $publisher->expects($this->once())
      ->method('publish')
      ->with(self::TOPIC, $data, false);

    return $publisher;
  }

  /**
   * @param array<string, array{0: mixed, 1: mixed}> $changeSet
   */
  private function updateArgs(ImageView $image, array $changeSet): PostUpdateEventArgs {
    $unitOfWork = $this->createStub(UnitOfWork::class);
    $unitOfWork->method('getEntityChangeSet')->willReturn($changeSet);

    $entityManager = $this->createStub(EntityManagerInterface::class);
    $entityManager->method('getUnitOfWork')->willReturn($unitOfWork);

    return new PostUpdateEventArgs($image, $entityManager);
  }

  #[Test]
  public function itPublishesImageAddedWhenPublicImageIsPersisted(): void {
    $image = $this->imageView(true);
    $publisher = $this->publisherExpecting(['event' => 'image_added', 'image' => self::NORMALIZED]);

    $listener = new ImageFeedChangeListener($publisher, $this->normalizer($image));
    $listener->postPersist($image, new PostPersistEventArgs($image, $this->createStub(EntityManagerInterface::class)));
  }

  #[Test]
  public function itPublishesNothingWhenNonPublicImageIsPersisted(): void {
    $image = $this->imageView(false);

    $listener = new ImageFeedChangeListener($this->publisherExpectingNothing(), $this->normalizer());
    $listener->postPersist($image, new PostPersistEventArgs($image, $this->createStub(EntityManagerInterface::class)));
  }

  #[Test]
  public function itPublishesImageRemovedWhenPublicImageIsRemoved(): void {
    $image = $this->imageView(true);
    $publisher = $this->publisherExpecting(['event' => 'image_removed', 'imageId' => self::UUID]);

    $listener = new ImageFeedChangeListener($publisher, $this->normalizer());
    $listener->postRemove($image, new PostRemoveEventArgs($image, $this->createStub(EntityManagerInterface::class)));
  }

  #[Test]
  public function itPublishesNothingWhenNonPublicImageIsRemoved(): void {
    $image = $this->imageView(false);

    $listener = new ImageFeedChangeListener($this->publisherExpectingNothing(), $this->normalizer());
    $listener->postRemove($image, new PostRemoveEventArgs($image, $this->createStub(EntityManagerInterface::class)));
  }

  #[Test]
  public function itPublishesImageAddedWhenImageBecomesPublic(): void {
    $image = $this->imageView(true);
    $publisher = $this->publisherExpecting(['event' => 'image_added', 'image' => self::NORMALIZED]);

    $listener = new ImageFeedChangeListener($publisher, $this->normalizer($image));
    $listener->postUpdate($image, $this->updateArgs($image, ['attributes.isPublic' => [false, true]]));
  }

  #[Test]
  public function itPublishesImageRemovedWithoutBodyWhenImageBecomesNonPublic(): void {
    $image = $this->imageView(false);
    $publisher = $this->publisherExpecting(['event' => 'image_removed', 'imageId' => self::UUID]);

    $normalizer = $this->createMock(NormalizerInterface::class);
    $normalizer->expects($this->never())->method('normalize');

    $listener = new ImageFeedChangeListener($publisher, $normalizer);
    $listener->postUpdate($image, $this->updateArgs($image, ['attributes.isPublic' => [true, false]]));
  }

  /**
   * @return array<string, array{0: string}>
   */
  public static function relevantChangeProvider(): array {
    return [
      'description' => ['attributes.description'],
      'file name' => ['attributes.fileName'],
      'bookmark count' => ['bookmarkCount'],
    ];
  }

  #[Test]
  #[DataProvider('relevantChangeProvider')]
  public function itPublishesImageUpdatedForRelevantChangeOnPublicImage(string $field): void {
    $image = $this->imageView(true);
    $publisher = $this->publisherExpecting(['event' => 'image_updated', 'image' => self::NORMALIZED]);

    $listener = new ImageFeedChangeListener($publisher, $this->normalizer($image));
    $listener->postUpdate($image, $this->updateArgs($image, [$field => ['old', 'new']]));
  }

  #[Test]
  public function itPublishesNothingForIrrelevantChangeOnPublicImage(): void {
    $image = $this->imageView(true);

    $listener = new ImageFeedChangeListener($this->publisherExpectingNothing(), $this->normalizer());
    $listener->postUpdate($image, $this->updateArgs($image, ['attributes.views' => [1, 2]]));
  }

  #[Test]
  #[DataProvider('relevantChangeProvider')]
  public function itPublishesNothingForRelevantChangeOnNonPublicImage(string $field): void {
    $image = $this->imageView(false);

    $listener = new ImageFeedChangeListener($this->publisherExpectingNothing(), $this->normalizer());
    $listener->postUpdate($image, $this->updateArgs($image, [$field => ['old', 'new']]));
  }
}
