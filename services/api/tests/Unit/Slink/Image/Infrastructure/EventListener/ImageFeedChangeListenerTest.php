<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Infrastructure\EventListener;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
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
use Slink\Image\Infrastructure\Message\ImageFeedChanged;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[Group('sc-886')]
final class ImageFeedChangeListenerTest extends TestCase {
  private const UUID = 'image-uuid-1';

  private function imageView(bool $isPublic): ImageView {
    $image = $this->createStub(ImageView::class);
    $image->method('getAttributes')->willReturn(ImageAttributes::create('photo.png', 'a photo', $isPublic));
    $image->method('getUuid')->willReturn(self::UUID);

    return $image;
  }

  private function busExpectingNothing(): MockObject&MessageBusInterface {
    $bus = $this->createMock(MessageBusInterface::class);
    $bus->expects($this->never())->method('dispatch');

    return $bus;
  }

  private function busExpecting(string $eventType): MockObject&MessageBusInterface {
    $bus = $this->createMock(MessageBusInterface::class);
    $bus->expects($this->once())
      ->method('dispatch')
      ->with($this->callback(static function (ImageFeedChanged $message) use ($eventType): bool {
        self::assertSame(self::UUID, $message->imageId);
        self::assertSame($eventType, $message->eventType->value);
        self::assertSame(['imageId', 'eventType'], array_keys(get_object_vars($message)));

        return true;
      }))
      ->willReturnCallback(static fn (object $message): Envelope => new Envelope($message));

    return $bus;
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
  public function itQueuesImageAddedWhenPublicImageIsPersisted(): void {
    $image = $this->imageView(true);
    $messageBus = $this->busExpecting('image_added');

    $listener = new ImageFeedChangeListener($messageBus);
    $listener->postPersist($image, new PostPersistEventArgs($image, $this->createStub(EntityManagerInterface::class)));
  }

  #[Test]
  public function itQueuesNothingWhenNonPublicImageIsPersisted(): void {
    $image = $this->imageView(false);

    $listener = new ImageFeedChangeListener($this->busExpectingNothing());
    $listener->postPersist($image, new PostPersistEventArgs($image, $this->createStub(EntityManagerInterface::class)));
  }

  private function flushDeleting(ImageView $image, int $claimedRows): OnFlushEventArgs {
    $connection = $this->createStub(Connection::class);
    $connection->method('quoteIdentifier')->willReturnArgument(0);
    $connection->method('update')->willReturn($claimedRows);

    $unitOfWork = $this->createStub(UnitOfWork::class);
    $unitOfWork->method('getScheduledEntityDeletions')->willReturn([$image]);

    $em = $this->createStub(EntityManagerInterface::class);
    $em->method('getConnection')->willReturn($connection);
    $em->method('getUnitOfWork')->willReturn($unitOfWork);

    return new OnFlushEventArgs($em);
  }

  #[Test]
  public function itQueuesImageRemovedWhenTheDeletedRowWasStillPublic(): void {
    $image = $this->imageView(false);
    $messageBus = $this->busExpecting('image_removed');

    $listener = new ImageFeedChangeListener($messageBus);
    $listener->onFlush($this->flushDeleting($image, 1));
  }

  #[Test]
  public function itQueuesNothingWhenTheDeletedRowWasAlreadyHidden(): void {
    $image = $this->imageView(true);

    $listener = new ImageFeedChangeListener($this->busExpectingNothing());
    $listener->onFlush($this->flushDeleting($image, 0));
  }

  #[Test]
  public function itQueuesImageAddedWhenImageBecomesPublic(): void {
    $image = $this->imageView(true);
    $messageBus = $this->busExpecting('image_added');

    $listener = new ImageFeedChangeListener($messageBus);
    $listener->postUpdate($image, $this->updateArgs($image, ['attributes.isPublic' => [false, true]]));
  }

  #[Test]
  public function itQueuesImageRemovedWithoutBodyWhenImageBecomesNonPublic(): void {
    $image = $this->imageView(false);
    $messageBus = $this->busExpecting('image_removed');

    $listener = new ImageFeedChangeListener($messageBus);
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
  public function itQueuesImageUpdatedForRelevantChangeOnPublicImage(string $field): void {
    $image = $this->imageView(true);
    $messageBus = $this->busExpecting('image_updated');

    $listener = new ImageFeedChangeListener($messageBus);
    $listener->postUpdate($image, $this->updateArgs($image, [$field => ['old', 'new']]));
  }

  #[Test]
  public function itQueuesNothingForIrrelevantChangeOnPublicImage(): void {
    $image = $this->imageView(true);

    $listener = new ImageFeedChangeListener($this->busExpectingNothing());
    $listener->postUpdate($image, $this->updateArgs($image, ['attributes.views' => [1, 2]]));
  }

  #[Test]
  #[DataProvider('relevantChangeProvider')]
  public function itQueuesNothingForRelevantChangeOnNonPublicImage(string $field): void {
    $image = $this->imageView(false);

    $listener = new ImageFeedChangeListener($this->busExpectingNothing());
    $listener->postUpdate($image, $this->updateArgs($image, [$field => ['old', 'new']]));
  }
}
