<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Shared\Domain;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\SnapshotRepository;
use EventSauce\EventSourcing\Snapshotting\SnapshottingBehaviour;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Bookmark\Domain\Bookmark;
use Slink\Collection\Domain\Collection;
use Slink\Comment\Domain\Comment;
use Slink\Image\Domain\Image;
use Slink\Notification\Domain\Notification;
use Slink\Settings\Domain\Settings;
use Slink\Share\Domain\Share;
use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Infrastructure\EventSourcing\AggregateRootWithAggregates;
use Slink\Tag\Domain\Tag;
use Slink\User\Domain\OAuthProvider;
use Slink\User\Domain\User;
use Symfony\Component\Yaml\Yaml;

#[Group('sc-1182')]
final class SnapshotRemovalTest extends TestCase {
  /**
   * @return iterable<string, array{string}>
   */
  public static function retiredClasses(): iterable {
    yield 'snapshot repository factory' => ['Slink\Shared\Infrastructure\Persistence\EventStore\SnapshotRepositoryFactory'];
    yield 'doctrine snapshot repository' => ['Slink\Shared\Infrastructure\Persistence\EventStore\Repository\DoctrineSnapshotRepository'];
    yield 'snapshot entity' => ['Slink\Shared\Infrastructure\Persistence\EventStore\Entity\SnapshotEntity'];
    yield 'snapshot store base' => ['Slink\Shared\Infrastructure\Persistence\EventStore\AbstractSnapshotStoreRepository'];
    yield 'replaying store base' => ['Slink\Shared\Infrastructure\Persistence\EventStore\AbstractStoreRepository'];
    yield 'aggregate value resolver' => ['Slink\Shared\Application\Http\RequestValueResolver\AggregateRootValueResolver'];
  }

  /**
   * @return iterable<string, array{class-string}>
   */
  public static function aggregates(): iterable {
    yield 'Bookmark' => [Bookmark::class];
    yield 'Collection' => [Collection::class];
    yield 'Comment' => [Comment::class];
    yield 'Image' => [Image::class];
    yield 'Notification' => [Notification::class];
    yield 'OAuthProvider' => [OAuthProvider::class];
    yield 'Settings' => [Settings::class];
    yield 'Share' => [Share::class];
    yield 'Tag' => [Tag::class];
    yield 'User' => [User::class];
  }

  #[Test]
  #[DataProvider('retiredClasses')]
  public function aRetiredClassNoLongerExists(string $class): void {
    self::assertFalse(\class_exists($class));
  }

  /**
   * @param class-string $aggregate
   */
  #[Test]
  #[DataProvider('aggregates')]
  public function anAggregateNoLongerOffersSnapshots(string $aggregate): void {
    self::assertSame([], \array_values(\array_filter(
      ['createSnapshot', 'createSnapshotState', 'reconstituteFromSnapshot', 'reconstituteFromSnapshotState'],
      static fn (string $method): bool => \method_exists($aggregate, $method),
    )));
  }

  #[Test]
  public function theAggregateBaseKeepsItsEventBehaviourWithoutSnapshotting(): void {
    $base = new \ReflectionClass(AbstractAggregateRoot::class);

    self::assertFalse($base->implementsInterface(AggregateRootWithSnapshotting::class));
    self::assertNotContains(SnapshottingBehaviour::class, $base->getTraitNames());
    self::assertContains(AggregateRootWithAggregates::class, $base->getTraitNames());
    self::assertTrue($base->implementsInterface(AggregateRoot::class));
  }

  #[Test]
  public function theEventSauceConfigurationDeclaresNoSnapshotRepository(): void {
    $config = Yaml::parseFile(\dirname(__DIR__, 5) . '/config/services/event_sauce.yaml');
    self::assertIsArray($config);
    self::assertIsArray($config['services']);

    self::assertArrayNotHasKey('event_sauce.snapshot_repository', $config['services']);
    self::assertArrayNotHasKey(SnapshotRepository::class, $config['services']);
  }
}
