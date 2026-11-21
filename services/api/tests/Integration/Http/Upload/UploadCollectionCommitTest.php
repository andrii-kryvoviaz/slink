<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Upload;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1607')]
final class UploadCollectionCommitTest extends HttpTestCase {
  private const string FAIL_TRIGGER = 'fail_upload_item_insert';

  private string $ownerToken = '';

  protected function setUp(): void {
    parent::setUp();

    $this->createUser('upload-commit-owner@local.test', 'uploadcommitowner', self::PASSWORD);
    $this->ownerToken = $this->login('uploadcommitowner', self::PASSWORD);
  }

  private function connection(): Connection {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return $entityManager->getConnection();
  }

  private function createTag(string $name): string {
    $status = $this->apiRequest('POST', '/api/tags', $this->ownerToken, ['CONTENT_TYPE' => 'application/json'], \json_encode(['name' => $name], JSON_THROW_ON_ERROR));

    self::assertSame(201, $status, (string) $this->client->getResponse()->getContent());

    return $this->extractId((string) $this->client->getResponse()->getContent());
  }

  private function imageRows(): int {
    return (int) $this->connection()->fetchOne('SELECT COUNT(*) FROM "image"');
  }

  private function tagLinks(): int {
    return (int) $this->connection()->fetchOne('SELECT COUNT(*) FROM image_to_tag');
  }

  private function itemRows(string $collectionId): int {
    return $this->countRowsByColumn('collection_item', 'collection_id', $collectionId);
  }

  private function pngBytes(): string {
    $sample = $this->sampleImage();
    $bytes = (string) \file_get_contents($sample->getPathname());
    \unlink($sample->getPathname());

    return $bytes;
  }

  private function fileOf(string $bytes): UploadedFile {
    $temp = \tempnam(\sys_get_temp_dir(), 'slink_commit_') . '.png';
    \file_put_contents($temp, $bytes);

    return new UploadedFile($temp, 'sample.png', 'image/png', null, true);
  }

  /**
   * @param list<string> $tagIds
   * @param list<string> $collectionIds
   */
  private function upload(string $bytes, array $tagIds, array $collectionIds): int {
    $this->client->request(
      'POST',
      '/api/upload',
      ['tagIds' => $tagIds, 'collectionIds' => $collectionIds],
      ['image' => $this->fileOf($bytes)],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->ownerToken],
    );

    return $this->client->getResponse()->getStatusCode();
  }

  private function failItemInsertsInto(string $collectionId): void {
    $this->connection()->executeStatement(
      'CREATE TRIGGER ' . self::FAIL_TRIGGER . ' BEFORE INSERT ON collection_item WHEN NEW.collection_id = '
      . $this->connection()->quote($collectionId)
      . " BEGIN SELECT RAISE(ABORT, 'injected item insert failure'); END",
    );
  }

  private function resetServices(): void {
    $this->client->getKernel()->boot();
  }

  private function allowItemInserts(): void {
    $this->connection()->executeStatement('DROP TRIGGER IF EXISTS ' . self::FAIL_TRIGGER);
  }

  /**
   * @return list<string>
   */
  private function itemIdsOf(string $collectionId): array {
    /** @var list<string> $itemIds */
    $itemIds = $this->connection()->fetchFirstColumn('SELECT item_id FROM collection_item WHERE collection_id = ?', [$collectionId]);

    return $itemIds;
  }

  #[Test]
  public function anUploadWhoseCollectionWriteFailsLeavesNoImageNoTagLinkAndNoCollectionItem(): void {
    $tagId = $this->createTag('commit-failure-tag');
    $firstId = $this->createCollection($this->ownerToken);
    $secondId = $this->createCollection($this->ownerToken);
    $this->failItemInsertsInto($secondId);

    try {
      $status = $this->upload($this->pngBytes(), [$tagId], [$firstId, $secondId]);
    } finally {
      $this->allowItemInserts();
    }

    self::assertGreaterThanOrEqual(400, $status, (string) $this->client->getResponse()->getContent());
    self::assertSame(0, $this->imageRows(), 'The failed upload left an image row.');
    self::assertSame(0, $this->tagLinks(), 'The failed upload left a tag link.');
    self::assertSame(0, $this->itemRows($firstId), 'The failed upload left an item in the first collection.');
    self::assertSame(0, $this->itemRows($secondId), 'The failed upload left an item in the second collection.');
  }

  #[Test]
  public function retryingTheSameFileAfterAFailedCommitSucceedsAndListsTheImageOnceInEachCollection(): void {
    $tagId = $this->createTag('commit-retry-tag');
    $firstId = $this->createCollection($this->ownerToken);
    $secondId = $this->createCollection($this->ownerToken);
    $bytes = $this->pngBytes();
    $this->failItemInsertsInto($secondId);

    try {
      $failed = $this->upload($bytes, [$tagId], [$firstId, $secondId]);
    } finally {
      $this->allowItemInserts();
    }

    self::assertGreaterThanOrEqual(400, $failed, (string) $this->client->getResponse()->getContent());

    $this->resetServices();

    $retry = $this->upload($bytes, [$tagId], [$firstId, $secondId]);

    self::assertSame(201, $retry, 'The retry was refused: ' . (string) $this->client->getResponse()->getContent());

    $imageId = $this->extractId((string) $this->client->getResponse()->getContent());
    self::assertSame(1, $this->imageRows());
    self::assertSame(1, $this->tagLinks());
    self::assertSame([$imageId], $this->itemIdsOf($firstId));
    self::assertSame([$imageId], $this->itemIdsOf($secondId));
  }
}
