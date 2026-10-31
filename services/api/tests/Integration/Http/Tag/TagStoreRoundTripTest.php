<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Tag;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use EventSauce\EventSourcing\Header;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\MessageBus\Event\EventDispatcher;
use Slink\Tag\Application\Command\CreateTag\CreateTagCommand;
use Slink\Tag\Domain\Event\TagWasCreated;
use Slink\Tag\Domain\Event\TagWasDeleted;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;
use Slink\Tag\Domain\Tag;
use Slink\Tag\Domain\ValueObject\TagName;
use Slink\Tag\Domain\ValueObject\TagPath;
use Slink\Tag\Infrastructure\EventConsumer\TagCascadeDeleteConsumer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tests\Integration\Http\Double\RecordingMessageDispatcher;
use Tests\Integration\Http\Store\ViewBackedStoreRoundTripTestCase;

/**
 * @extends ViewBackedStoreRoundTripTestCase<Tag>
 */
#[Group('sc-1175')]
final class TagStoreRoundTripTest extends ViewBackedStoreRoundTripTestCase {
  private const string TIMESTAMP_FORMAT = 'Y-m-d H:i:s';
  private const string UNKNOWN_TAG_ID = '11111111-2222-4333-8444-555555555555';

  private string $ownerId = '';
  private string $ownerToken = '';

  #[\Override]
  protected function createAggregate(): ID {
    $this->bootOwner();
    $parentId = $this->createRootTag('round-trip-parent');
    $childId = $this->createChildOf($parentId, 'round-trip-child');
    self::assertSame(204, $this->moveTag($parentId, $this->createRootTag('round-trip-bucket')));

    return ID::fromString($childId);
  }

  #[\Override]
  protected function fetch(ID $id): Tag {
    return $this->tagStore()->get($id);
  }

  #[\Override]
  protected function aggregateState(object $aggregate): array {
    return [
      'name' => $aggregate->getName()->getValue(),
      'path' => $aggregate->getPath()->getValue(),
      'parentId' => $aggregate->getParentId()?->toString(),
      'ownerId' => $aggregate->getUserId()->toString(),
      'createdAt' => $aggregate->getCreatedAt()?->format(self::TIMESTAMP_FORMAT),
      'updatedAt' => $aggregate->getUpdatedAt()?->format(self::TIMESTAMP_FORMAT),
    ];
  }

  #[\Override]
  protected function storedState(ID $id): array {
    $row = $this->readModel()->fetchAssociative('SELECT * FROM "tag" WHERE uuid = ?', [$id->toString()]);
    self::assertIsArray($row);

    return [
      'name' => $row['name'],
      'path' => $row['path'],
      'parentId' => $row['parent_id'],
      'ownerId' => $row['user_id'],
      'createdAt' => $this->secondsOf($row['created_at']),
      'updatedAt' => $this->secondsOf($row['updated_at']),
    ];
  }

  #[\Override]
  protected function rootTable(): string {
    return 'tag';
  }

  #[Test]
  public function creatingMovingAndDeletingATagUpdatesItsRowAndAppendsNothing(): void {
    $this->bootOwner();
    $logBefore = $this->eventLogSize();

    $parentId = $this->createRootTag('parent');
    $childId = $this->createChildOf($parentId, 'child');
    $bucketId = $this->createRootTag('bucket');
    self::assertSame('#parent/child', $this->pathOf($childId));

    self::assertSame(204, $this->moveTag($childId, $bucketId));
    self::assertSame('#bucket/child', $this->pathOf($childId));
    self::assertSame($bucketId, $this->tagColumn($childId, 'parent_id'));

    self::assertSame(204, $this->deleteTag($childId));
    self::assertSame(0, $this->countRowsByColumn('tag', 'uuid', $childId));

    self::assertSame($logBefore, $this->eventLogSize());
  }

  #[Test]
  public function storingDispatchesDecoratedMessagesWithoutAppendingThem(): void {
    $dispatcher = new RecordingMessageDispatcher();
    static::getContainer()->set(EventDispatcher::class, $dispatcher);
    $tagId = ID::generate();
    $ownerId = ID::generate();

    $this->tagStore()->store(Tag::create($tagId, $ownerId, TagName::fromString('decorated')));

    self::assertCount(1, $dispatcher->messages);
    $message = $dispatcher->messages[0];
    self::assertInstanceOf(TagWasCreated::class, $message->payload());
    self::assertSame($tagId->toString(), $message->aggregateRootId()?->toString());
    self::assertSame('slink.tag.domain.event.tag_was_created', $message->header(Header::EVENT_TYPE));
    self::assertSame('slink.tag.domain.tag', $message->header(Header::AGGREGATE_ROOT_TYPE));
    self::assertNotNull($message->header(Header::AGGREGATE_ROOT_VERSION));
    self::assertNotNull($message->header(Header::TIME_OF_RECORDING));
    self::assertNotNull($message->header(Header::TIME_OF_RECORDING_FORMAT));
    self::assertSame(0, $this->loggedEvents($tagId));
  }

  #[Test]
  public function reloadedTagHoldsTheStoredNamePathParentOwnerAndTimestamps(): void {
    $this->bootOwner();
    $parentId = $this->createRootTag('parent');
    $childId = $this->createChildOf($parentId, 'child');
    $this->readModel()->executeStatement(
      'UPDATE "tag" SET created_at = ?, updated_at = ? WHERE uuid = ?',
      ['2021-02-03 04:05:06', '2022-03-04 05:06:07', $childId],
    );

    $tag = $this->reload(ID::fromString($childId));

    self::assertSame('child', $tag->getName()->getValue());
    self::assertSame('#parent/child', $tag->getPath()->getValue());
    self::assertSame($parentId, $tag->getParentId()?->toString());
    self::assertSame($this->ownerId, $tag->getUserId()->toString());
    self::assertSame('2021-02-03 04:05:06', $tag->getCreatedAt()?->format(self::TIMESTAMP_FORMAT));
    self::assertSame('2022-03-04 05:06:07', $tag->getUpdatedAt()?->format(self::TIMESTAMP_FORMAT));
  }

  #[Test]
  public function aTagRowWithoutTimestampsReloadsWithoutThem(): void {
    $this->bootOwner();
    $tagId = $this->createRootTag('legacy');
    $this->readModel()->executeStatement(
      'UPDATE "tag" SET created_at = NULL, updated_at = NULL WHERE uuid = ?',
      [$tagId],
    );

    $tag = $this->reload(ID::fromString($tagId));

    self::assertNull($tag->getCreatedAt());
    self::assertNull($tag->getUpdatedAt());
  }

  #[Test]
  public function deletingATagThatDoesNotExistAnswersNotFound(): void {
    $this->bootOwner();

    self::assertSame(404, $this->deleteTag(self::UNKNOWN_TAG_ID));
  }

  #[Test]
  public function deletingAnAlreadyDeletedTagAnswersNotFound(): void {
    $this->bootOwner();
    $tagId = $this->createRootTag('short-lived');

    self::assertSame(204, $this->deleteTag($tagId));
    self::assertSame(404, $this->deleteTag($tagId));
  }

  #[Test]
  public function creatingATagUnderAnUnknownParentAnswersNotFound(): void {
    $this->bootOwner();

    $status = $this->apiRequest(
      'POST',
      '/api/tags',
      $this->ownerToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['name' => 'orphan', 'parentId' => self::UNKNOWN_TAG_ID], JSON_THROW_ON_ERROR),
    );

    self::assertSame(404, $status);
    self::assertSame(0, $this->countRowsByColumn('tag', 'name', 'orphan'));
  }

  #[Test]
  public function movingATagThatDoesNotExistAnswersNotFound(): void {
    $this->bootOwner();

    self::assertSame(404, $this->moveTag(self::UNKNOWN_TAG_ID, null));
  }

  #[Test]
  public function deletingAParentRemovesEveryDescendantRowAndAppendsNothing(): void {
    $this->bootOwner();
    $parentId = $this->createRootTag('parent');
    $childId = $this->createChildOf($parentId, 'child');
    $siblingId = $this->createChildOf($parentId, 'sibling');
    $leafId = $this->createChildOf($childId, 'leaf');
    $unrelatedId = $this->createRootTag('unrelated');
    $logBefore = $this->eventLogSize();

    self::assertSame(204, $this->deleteTag($parentId));

    foreach ([$parentId, $childId, $siblingId, $leafId] as $removedId) {
      self::assertSame(0, $this->countRowsByColumn('tag', 'uuid', $removedId), $removedId);
    }

    self::assertSame('#unrelated', $this->pathOf($unrelatedId));
    self::assertSame($logBefore, $this->eventLogSize());
  }

  #[Test]
  public function cascadingToAChildAlreadyGoneAnswersNotFound(): void {
    $this->bootOwner();
    /** @var TagCascadeDeleteConsumer $consumer */
    $consumer = static::getContainer()->get(TagCascadeDeleteConsumer::class);
    $event = new TagWasDeleted(ID::generate(), $this->ownerId, [self::UNKNOWN_TAG_ID]);

    self::assertCausedBy(NotFoundException::class, $this->failureOf(
      static fn () => $consumer->handleTagWasDeleted($event),
    ));
  }

  #[Test]
  public function afterAMoveEveryDescendantReloadsTheRewrittenPath(): void {
    $this->bootOwner();
    $parentId = $this->createRootTag('parent');
    $childId = $this->createChildOf($parentId, 'child');
    $leafId = $this->createChildOf($childId, 'leaf');
    $bucketId = $this->createRootTag('bucket');

    self::assertSame(204, $this->moveTag($parentId, $bucketId));

    self::assertSame('#bucket/parent/child', $this->reload(ID::fromString($childId))->getPath()->getValue());
    self::assertSame('#bucket/parent/child/leaf', $this->reload(ID::fromString($leafId))->getPath()->getValue());
  }

  #[Test]
  public function aTagCreatedUnderAMovedDescendantBuildsOnTheRewrittenPath(): void {
    $this->bootOwner();
    $parentId = $this->createRootTag('parent');
    $childId = $this->createChildOf($parentId, 'child');
    $bucketId = $this->createRootTag('bucket');
    self::assertSame(204, $this->moveTag($parentId, $bucketId));

    $newLeafId = $this->createChildOf($childId, 'new-leaf');

    self::assertSame('#bucket/parent/child/new-leaf', $this->pathOf($newLeafId));
  }

  #[Test]
  public function theSecondOfTwoConcurrentWritersFailsAndTheFirstWritersValuesStay(): void {
    $tagId = $this->createAggregate();
    $firstContainer = $this->independentContainer();
    $secondContainer = $this->independentContainer();
    $firstLoad = $this->tagStoreOf($firstContainer)->get($tagId);
    $secondLoad = $this->tagStoreOf($secondContainer)->get($tagId);

    $this->moveAndStore($firstContainer, $firstLoad, '#first-writer');

    self::assertCausedBy(OptimisticLockException::class, $this->failureOf(
      fn () => $this->moveAndStore($secondContainer, $secondLoad, '#second-writer'),
    ));
    self::assertSame('#first-writer', $this->pathOf($tagId->toString()));
    self::assertSame('#first-writer', $this->reload($tagId)->getPath()->getValue());
  }

  #[Test]
  public function viewBackedTagsAndReplayedCollectionsWorkSideBySide(): void {
    $this->bootOwner();

    $tagId = $this->createRootTag('side-by-side');
    $collectionId = $this->createCollection($this->ownerToken);

    self::assertSame(0, $this->loggedEvents(ID::fromString($tagId)));
    self::assertGreaterThan(0, $this->loggedEvents(ID::fromString($collectionId)));
    self::assertSame('side-by-side', $this->reload(ID::fromString($tagId))->getName()->getValue());

    /** @var CollectionStoreRepositoryInterface $collections */
    $collections = static::getContainer()->get(CollectionStoreRepositoryInterface::class);
    self::assertSame('functional-collection', $collections->get(ID::fromString($collectionId))->getName()->toString());
  }

  private function bootOwner(): void {
    $this->ownerId = $this->createUser('tag-store-owner@local.test', 'tagstoreowner', self::PASSWORD);
    $this->ownerToken = $this->login('tagstoreowner', self::PASSWORD);
  }

  private function createRootTag(string $name): string {
    return $this->dispatchCreate(new CreateTagCommand($name));
  }

  private function createChildOf(string $parentId, string $name): string {
    return $this->dispatchCreate(new CreateTagCommand($name, $parentId));
  }

  private function dispatchCreate(CreateTagCommand $command): string {
    $this->entityManager()->clear();
    /** @var ID $tagId */
    $tagId = $this->commandBus()->handleSync($command->withContext(['userId' => $this->ownerId]));

    return $tagId->toString();
  }

  private function moveTag(string $tagId, ?string $newParentId): int {
    $this->entityManager()->clear();

    return $this->apiRequest(
      'PATCH',
      '/api/tags/move',
      $this->ownerToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $tagId, 'newParentId' => $newParentId], JSON_THROW_ON_ERROR),
    );
  }

  private function deleteTag(string $tagId): int {
    $this->entityManager()->clear();

    return $this->apiRequest('DELETE', \sprintf('/api/tags/%s', $tagId), $this->ownerToken);
  }

  private function moveAndStore(ContainerInterface $container, Tag $tag, string $path): void {
    $tag->move(null, TagPath::fromString($path));
    $this->tagStoreOf($container)->store($tag);

    /** @var EntityManagerInterface $entityManager */
    $entityManager = $container->get(EntityManagerInterface::class);
    $entityManager->flush();
  }

  private function tagStore(): TagStoreRepositoryInterface {
    return $this->tagStoreOf(static::getContainer());
  }

  private function tagStoreOf(ContainerInterface $container): TagStoreRepositoryInterface {
    /** @var TagStoreRepositoryInterface $store */
    $store = $container->get(TagStoreRepositoryInterface::class);

    return $store;
  }

  private function pathOf(string $tagId): mixed {
    return $this->tagColumn($tagId, 'path');
  }

  private function tagColumn(string $tagId, string $column): mixed {
    return $this->readModel()->fetchOne(\sprintf('SELECT %s FROM "tag" WHERE uuid = ?', $column), [$tagId]);
  }
}
