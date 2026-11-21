<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Upload;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1607')]
final class UploadRepeatedCollectionTest extends HttpTestCase {
  private string $ownerToken = '';

  protected function setUp(): void {
    parent::setUp();

    $this->createUser('upload-repeat-owner@local.test', 'uploadrepeatowner', self::PASSWORD);
    $this->ownerToken = $this->login('uploadrepeatowner', self::PASSWORD);
  }

  #[Test]
  public function aRepeatedCollectionIdAddsTheImageOnceAndAnswers201(): void {
    $collectionId = $this->createCollection($this->ownerToken);
    $sample = $this->sampleImage();
    $file = new UploadedFile($sample->getPathname(), 'sample.png', 'image/png', null, true);

    $this->client->request(
      'POST',
      '/api/upload',
      ['collectionIds' => [$collectionId, $collectionId]],
      ['image' => $file],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->ownerToken],
    );

    self::assertSame(201, $this->client->getResponse()->getStatusCode(), (string) $this->client->getResponse()->getContent());

    $imageId = $this->extractId((string) $this->client->getResponse()->getContent());

    self::assertSame(1, $this->countRowsByColumn('collection_item', 'collection_id', $collectionId));
    self::assertSame(1, $this->countRowsByColumn('collection_item', 'item_id', $imageId));
  }
}
