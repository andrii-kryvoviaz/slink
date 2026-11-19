<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Collection;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1605')]
final class CollectionCommandFailureTest extends HttpTestCase {
  private function connection(): Connection {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return $entityManager->getConnection();
  }

  /**
   * @return list<string>
   */
  private function itemIdsOf(string $collectionId): array {
    /** @var list<string> $itemIds */
    $itemIds = $this->connection()->fetchFirstColumn(
      'SELECT item_id FROM collection_item WHERE collection_id = ? ORDER BY position',
      [$collectionId],
    );

    return $itemIds;
  }

  #[Test]
  public function aCommandThatFailsAfterStoringItsCollectionChangeLeavesTheCollectionUnchanged(): void {
    $this->createUser('collection-failure-owner@local.test', 'collectionfailureowner', self::PASSWORD);
    $token = $this->login('collectionfailureowner', self::PASSWORD);
    $collectionId = $this->createCollection($token);
    $keptId = $this->uploadImage($token, false);
    $failedId = $this->uploadImage($token, false);
    $this->addImageToCollection($token, $collectionId, $keptId);
    $connection = $this->connection();
    $connection->executeStatement(
      'CREATE TRIGGER fail_added_item_insert BEFORE INSERT ON collection_item WHEN NEW.item_id = '
      . $connection->quote($failedId)
      . " BEGIN SELECT RAISE(ABORT, 'injected item insert failure'); END",
    );

    try {
      $status = $this->apiRequest(
        'POST',
        \sprintf('/api/collection/%s/items/%s', $collectionId, $failedId),
        $token,
      );
    } finally {
      $connection->executeStatement('DROP TRIGGER fail_added_item_insert');
    }

    self::assertSame(500, $status, (string) $this->client->getResponse()->getContent());
    self::assertSame([$keptId], $this->itemIdsOf($collectionId));
  }
}
