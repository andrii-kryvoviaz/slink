<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Concurrency;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[Group('sc-1607')]
final class ConcurrentUploadIntoCollectionTest extends ConcurrentRequestTestCase {
  private string $ownerToken = '';

  protected function setUp(): void {
    parent::setUp();

    $this->createUser('upload-race-owner@local.test', 'uploadraceowner', self::PASSWORD);
    $this->ownerToken = $this->login('uploadraceowner', self::PASSWORD);
  }

  /**
   * @return array{status: int, body: string}
   */
  private function uploadFromAnotherKernel(string $collectionId): array {
    $request = Request::create(
      '/api/upload',
      'POST',
      ['collectionIds' => [$collectionId]],
      [],
      ['image' => $this->sampleImage()],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->ownerToken],
    );
    $response = $this->independentKernel()->handle($request);

    \ob_start();
    $response->sendContent();
    $body = (string) \ob_get_clean();

    return ['status' => $response->getStatusCode(), 'body' => $body];
  }

  /**
   * @return list<string>
   */
  private function itemIdsOf(string $collectionId): array {
    /** @var list<string> $itemIds */
    $itemIds = $this->connection()->fetchFirstColumn('SELECT item_id FROM collection_item WHERE collection_id = ?', [$collectionId]);
    \sort($itemIds);

    return $itemIds;
  }

  #[Test]
  public function uploadsRunningAtTheSameTimeIntoOneCollectionAllLandInItAndNoneAnswers409(): void {
    $collectionId = $this->createCollection($this->ownerToken);
    $overlapping = null;

    $this->beforeTheNextFlush(function () use (&$overlapping, $collectionId): void {
      $overlapping = $this->uploadFromAnotherKernel($collectionId);
    });

    $this->entityManager()->clear();
    $this->client->request(
      'POST',
      '/api/upload',
      ['collectionIds' => [$collectionId]],
      ['image' => $this->sampleImage()],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->ownerToken],
    );
    $response = $this->client->getResponse();

    self::assertNotNull($overlapping);
    self::assertSame(201, $overlapping['status'], $overlapping['body']);
    self::assertSame(201, $response->getStatusCode(), (string) $response->getContent());

    $firstId = $this->extractId((string) $response->getContent());
    $secondId = $this->extractId($overlapping['body']);
    $expected = [$firstId, $secondId];
    \sort($expected);

    self::assertNotSame($firstId, $secondId);
    self::assertSame($expected, $this->itemIdsOf($collectionId));
  }
}
