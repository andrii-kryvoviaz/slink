<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Concurrency;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('sc-1606')]
final class ConcurrentCollectionItemsTest extends ConcurrentRequestTestCase {
  private const array SUCCESS = [200, 201, 204];
  private const array ADDS = ['add', 'batchAdd'];

  private string $ownerToken = '';

  protected function setUp(): void {
    parent::setUp();

    $this->createUser('collection-race-owner@local.test', 'collectionraceowner', self::PASSWORD);
    $this->ownerToken = $this->login('collectionraceowner', self::PASSWORD);
  }

  /**
   * @return iterable<string, array{string, string}>
   */
  public static function overlappingWrites(): iterable {
    yield 'add overlapping a batch add' => ['add', 'batchAdd'];
    yield 'batch add overlapping an add' => ['batchAdd', 'add'];
    yield 'batch add overlapping a batch add' => ['batchAdd', 'batchAdd'];
    yield 'batch removal overlapping an add' => ['batchRemove', 'add'];
    yield 'add overlapping a batch removal' => ['add', 'batchRemove'];
    yield 'add overlapping a reorder' => ['add', 'reorder'];
    yield 'batch add overlapping a reorder' => ['batchAdd', 'reorder'];
    yield 'reorder overlapping an add' => ['reorder', 'add'];
    yield 'reorder overlapping a batch add' => ['reorder', 'batchAdd'];
  }

  private function itemPath(string $collectionId, string $imageId): string {
    return \sprintf('/api/collection/%s/items/%s', $collectionId, $imageId);
  }

  private function add(string $collectionId, string $imageId): int {
    return $this->apiRequest('POST', $this->itemPath($collectionId, $imageId), $this->ownerToken);
  }

  /**
   * @return array{status: int, body: string}
   */
  private function addFromAnotherKernel(string $collectionId, string $imageId): array {
    return $this->requestFromAnotherKernel('POST', $this->itemPath($collectionId, $imageId), $this->ownerToken);
  }

  /**
   * @param list<string> $orderedItemIds
   * @return array{collectionId: string, orderedItemIds: list<string>}
   */
  private function reorderBody(string $collectionId, array $orderedItemIds): array {
    return ['collectionId' => $collectionId, 'orderedItemIds' => $orderedItemIds];
  }

  /**
   * @param list<string> $orderedItemIds
   */
  private function reorder(string $collectionId, array $orderedItemIds): int {
    return $this->sendJson('PATCH', '/api/collection/items/order', $this->ownerToken, $this->reorderBody($collectionId, $orderedItemIds));
  }

  /**
   * @param list<string> $orderedItemIds
   * @return array{status: int, body: string}
   */
  private function reorderFromAnotherKernel(string $collectionId, array $orderedItemIds): array {
    return $this->requestFromAnotherKernel(
      'PATCH',
      '/api/collection/items/order',
      $this->ownerToken,
      $this->reorderBody($collectionId, $orderedItemIds),
    );
  }

  /**
   * @param list<string> $collectionIds
   * @return array{assignments: array<string, array{collectionIds: list<string>}>}
   */
  private function reassignBody(string $imageId, array $collectionIds): array {
    return ['assignments' => [$imageId => ['collectionIds' => $collectionIds]]];
  }

  /**
   * @param list<string> $collectionIds
   */
  private function reassign(string $imageId, array $collectionIds): int {
    return $this->sendJson('PUT', '/api/images/batch', $this->ownerToken, $this->reassignBody($imageId, $collectionIds));
  }

  /**
   * @param list<string> $collectionIds
   * @return array{status: int, body: string}
   */
  private function reassignFromAnotherKernel(string $imageId, array $collectionIds): array {
    return $this->requestFromAnotherKernel('PUT', '/api/images/batch', $this->ownerToken, $this->reassignBody($imageId, $collectionIds));
  }

  /**
   * @return list<string>
   */
  private function uploadImages(int $count): array {
    $imageIds = [];

    for ($index = 0; $index < $count; $index++) {
      $imageIds[] = $this->uploadImage($this->ownerToken, false);
    }

    return $imageIds;
  }

  /**
   * @param list<string> $imageIds
   */
  private function addAll(string $collectionId, array $imageIds): void {
    foreach ($imageIds as $imageId) {
      self::assertContains($this->add($collectionId, $imageId), self::SUCCESS, (string) $this->client->getResponse()->getContent());
    }
  }

  /**
   * @return list<string>
   */
  private function listedItemIds(string $collectionId): array {
    self::assertSame(
      200,
      $this->apiRequest('GET', \sprintf('/api/collection/%s/items', $collectionId), $this->ownerToken),
      (string) $this->client->getResponse()->getContent(),
    );

    /** @var array{data?: list<array{itemId: string, position: float|int}>} $payload */
    $payload = $this->responsePayload();
    $items = $payload['data'] ?? [];
    \usort($items, static fn (array $left, array $right): int => $left['position'] <=> $right['position']);

    return \array_column($items, 'itemId');
  }

  /**
   * @param list<string> $expected
   */
  private function assertListsExactly(array $expected, string $collectionId): void {
    $listed = $this->listedItemIds($collectionId);
    \sort($expected);
    \sort($listed);

    self::assertSame($expected, $listed);
  }

  /**
   * @param list<string> $reorderedIds
   * @return array{status: int, body: string}
   */
  private function performFromAnotherKernel(string $write, string $collectionId, string $imageId, array $reorderedIds): array {
    return match ($write) {
      'add' => $this->addFromAnotherKernel($collectionId, $imageId),
      'batchAdd' => $this->reassignFromAnotherKernel($imageId, [$collectionId]),
      'batchRemove' => $this->reassignFromAnotherKernel($imageId, []),
      'reorder' => $this->reorderFromAnotherKernel($collectionId, $reorderedIds),
      default => self::fail('Unknown write ' . $write),
    };
  }

  /**
   * @param list<string> $reorderedIds
   */
  private function perform(string $write, string $collectionId, string $imageId, array $reorderedIds): int {
    return match ($write) {
      'add' => $this->add($collectionId, $imageId),
      'batchAdd' => $this->reassign($imageId, [$collectionId]),
      'batchRemove' => $this->reassign($imageId, []),
      'reorder' => $this->reorder($collectionId, $reorderedIds),
      default => self::fail('Unknown write ' . $write),
    };
  }

  #[Test]
  public function concurrentAddsOfDifferentImagesIntoOneCollectionAllSucceedAndTheCollectionListsEveryImage(): void {
    $collectionId = $this->createCollection($this->ownerToken);
    [$first, $second, $third] = $this->uploadImages(3);
    $overlapping = [];

    $this->beforeTheNextFlush(function () use (&$overlapping, $collectionId, $second, $third): void {
      foreach ([$second, $third] as $imageId) {
        $overlapping[] = $this->addFromAnotherKernel($collectionId, $imageId);
      }
    });

    $status = $this->add($collectionId, $first);

    foreach ($overlapping as $answer) {
      self::assertContains($answer['status'], self::SUCCESS, $answer['body']);
    }
    self::assertCount(2, $overlapping);
    self::assertContains($status, self::SUCCESS, (string) $this->client->getResponse()->getContent());
    $this->assertListsExactly([$first, $second, $third], $collectionId);
  }

  #[Test]
  public function twoConcurrentAddsOfTheSameImageBothSucceedAndTheCollectionHoldsItOnce(): void {
    $collectionId = $this->createCollection($this->ownerToken);
    [$imageId] = $this->uploadImages(1);
    $overlapping = null;

    $this->beforeTheNextFlush(function () use (&$overlapping, $collectionId, $imageId): void {
      $overlapping = $this->addFromAnotherKernel($collectionId, $imageId);
    });

    $status = $this->add($collectionId, $imageId);
    $body = (string) $this->client->getResponse()->getContent();

    self::assertContains($overlapping['status'] ?? null, self::SUCCESS, $overlapping['body'] ?? '');
    self::assertContains($status, self::SUCCESS, $body);
    self::assertSame(1, $this->countRowsByColumn('collection_item', 'collection_id', $collectionId));
    self::assertSame([$imageId], $this->listedItemIds($collectionId));

    $alreadyInCollection = $this->add($collectionId, $imageId);
    self::assertSame($alreadyInCollection, $status, $body);
    self::assertSame(1, $this->countRowsByColumn('collection_item', 'collection_id', $collectionId));
  }

  #[Test]
  public function anItemAddedAfterAReorderAppearsAfterEveryReorderedItem(): void {
    $collectionId = $this->createCollection($this->ownerToken);
    [$first, $second, $third, $added] = $this->uploadImages(4);
    $this->addAll($collectionId, [$first, $second, $third]);
    self::assertContains($this->reorder($collectionId, [$third, $first, $second]), self::SUCCESS);

    self::assertContains($this->add($collectionId, $added), self::SUCCESS, (string) $this->client->getResponse()->getContent());

    self::assertSame([$third, $first, $second, $added], $this->listedItemIds($collectionId));
  }

  #[Test]
  public function anItemAddedWhileAReorderOfTheSameCollectionRunsEndsUpInTheCollection(): void {
    $collectionId = $this->createCollection($this->ownerToken);
    [$first, $second, $added] = $this->uploadImages(3);
    $this->addAll($collectionId, [$first, $second]);
    $overlapping = null;

    $this->beforeTheNextFlush(function () use (&$overlapping, $collectionId, $added): void {
      $overlapping = $this->addFromAnotherKernel($collectionId, $added);
    });

    $status = $this->reorder($collectionId, [$second, $first]);

    self::assertContains($overlapping['status'] ?? null, self::SUCCESS, $overlapping['body'] ?? '');
    self::assertContains($status, self::SUCCESS, (string) $this->client->getResponse()->getContent());
    $this->assertListsExactly([$first, $second, $added], $collectionId);
  }

  #[Test]
  #[DataProvider('overlappingWrites')]
  public function aCollectionWriteOverlappingAnotherCollectionWriteNeverAnswersConflict(string $write, string $overlap): void {
    $collectionId = $this->createCollection($this->ownerToken);
    [$first, $second, $mine, $theirs] = $this->uploadImages(4);
    $this->addAll($collectionId, [$first, $second]);

    if ($write === 'batchRemove') {
      $this->addAll($collectionId, [$mine]);
    }

    if ($overlap === 'batchRemove') {
      $this->addAll($collectionId, [$theirs]);
    }

    $overlapping = null;

    $this->beforeTheNextFlush(function () use (&$overlapping, $overlap, $collectionId, $theirs, $first, $second): void {
      $overlapping = $this->performFromAnotherKernel($overlap, $collectionId, $theirs, [$second, $first]);
    });

    $status = $this->perform($write, $collectionId, $mine, [$second, $first]);

    self::assertContains($overlapping['status'] ?? null, self::SUCCESS, $overlapping['body'] ?? '');
    self::assertContains($status, self::SUCCESS, (string) $this->client->getResponse()->getContent());

    $expected = [$first, $second];

    if (\in_array($write, self::ADDS, true)) {
      $expected[] = $mine;
    }

    if (\in_array($overlap, self::ADDS, true)) {
      $expected[] = $theirs;
    }

    $this->assertListsExactly($expected, $collectionId);
  }

  #[Test]
  public function ofTwoConcurrentReordersOfOneCollectionOneSucceedsTheOtherAnswersConflictAndTheWinningOrderStays(): void {
    $collectionId = $this->createCollection($this->ownerToken);
    [$first, $second, $third] = $this->uploadImages(3);
    $this->addAll($collectionId, [$first, $second, $third]);
    $winner = null;

    $this->beforeTheNextFlush(function () use (&$winner, $collectionId, $first, $second, $third): void {
      $winner = $this->reorderFromAnotherKernel($collectionId, [$third, $second, $first]);
    });

    $status = $this->reorder($collectionId, [$second, $third, $first]);

    self::assertContains($winner['status'] ?? null, self::SUCCESS, $winner['body'] ?? '');
    self::assertSame(409, $status, (string) $this->client->getResponse()->getContent());
    self::assertSame(['error' => ['title' => 'Conflict', 'message' => 'Conflict']], $this->responsePayload());
    self::assertSame([$third, $second, $first], $this->listedItemIds($collectionId));
  }
}
