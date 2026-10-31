<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Image;

use EventSauce\EventSourcing\Header;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Image\Application\Command\AddImageViewCount\AddImageViewCountCommand;
use Slink\Image\Domain\Enum\License;
use Slink\Image\Domain\Event\ImageWasCreated;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\MessageBus\Event\EventDispatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tests\Integration\Http\Double\RecordingMessageDispatcher;
use Tests\Integration\Http\Store\ViewBackedStoreRoundTripTestCase;

/**
 * @extends ViewBackedStoreRoundTripTestCase<Image>
 */
#[Group('sc-1179')]
final class ImageStoreRoundTripTest extends ViewBackedStoreRoundTripTestCase {
  use DrivesImageWrites;

  private const string DESCRIPTION = 'Tom & Jerry <b>"quoted"</b>';
  private const string STORED_DESCRIPTION = 'Tom &amp; Jerry &lt;b&gt;&quot;quoted&quot;&lt;/b&gt;';
  private const string UNKNOWN_IMAGE_ID = '11111111-2222-4333-8444-555555555555';
  private const string TIMESTAMP_FORMAT = 'Y-m-d H:i:s';

  #[\Override]
  protected function createAggregate(): ID {
    $this->bootActors();

    return ID::fromString($this->uploadTaggedLicensedImage(self::DESCRIPTION));
  }

  #[\Override]
  protected function fetch(ID $id): Image {
    return $this->imageStore()->get($id);
  }

  #[\Override]
  protected function aggregateState(object $aggregate): array {
    $attributes = $aggregate->getAttributes();
    $metadata = $aggregate->getMetadata();
    $tagIds = $aggregate->getTagIds();
    \sort($tagIds);

    return [
      'ownerId' => $aggregate->getUserId()?->toString(),
      'fileName' => $attributes->getFileName(),
      'description' => $attributes->getDescription(),
      'isPublic' => $attributes->isPublic(),
      'createdAt' => $attributes->getCreatedAt()->format(self::TIMESTAMP_FORMAT),
      'updatedAt' => $attributes->getUpdatedAt()?->format(self::TIMESTAMP_FORMAT),
      'views' => $attributes->getViews(),
      'size' => $metadata->getSize(),
      'mimeType' => $metadata->getMimeType(),
      'width' => $metadata->getWidth(),
      'height' => $metadata->getHeight(),
      'sha1Hash' => $metadata->getSha1Hash(),
      'tagIds' => $tagIds,
      'license' => $aggregate->getLicense()?->value,
    ];
  }

  #[\Override]
  protected function storedState(ID $id): array {
    $row = $this->readModel()->fetchAssociative('SELECT * FROM "image" WHERE uuid = ?', [$id->toString()]);
    self::assertIsArray($row);

    return [
      'ownerId' => $row['user_id'],
      'fileName' => $row['file_name'],
      'description' => $row['description'],
      'isPublic' => (bool) $row['is_public'],
      'createdAt' => $this->secondsOf($row['created_at']),
      'updatedAt' => $this->secondsOf($row['updated_at']),
      'views' => (int) $row['views'],
      'size' => (int) $row['size'],
      'mimeType' => $row['mime_type'],
      'width' => (int) $row['width'],
      'height' => (int) $row['height'],
      'sha1Hash' => $row['sha1_hash'],
      'tagIds' => $this->readModel()->fetchFirstColumn(
        'SELECT tag_id FROM image_to_tag WHERE image_id = ? ORDER BY tag_id',
        [$id->toString()],
      ),
      'license' => $this->storedLicense($id),
    ];
  }

  #[\Override]
  protected function rootTable(): string {
    return 'image';
  }

  #[Test]
  public function reloadedImageHoldsItsOwnerAttributesMetadataTagsAndLicense(): void {
    $id = $this->createAggregate();

    $image = $this->reload($id);

    self::assertSame($this->ownerId, $image->getUserId()?->toString());
    self::assertSame(self::STORED_DESCRIPTION, $image->getAttributes()->getDescription());
    self::assertTrue($image->getAttributes()->isPublic());
    self::assertSame(0, $image->getAttributes()->getViews());
    self::assertSame('image/png', $image->getMetadata()->getMimeType());
    self::assertSame(2, $image->getMetadata()->getWidth());
    self::assertSame(2, $image->getMetadata()->getHeight());
    self::assertSame([$this->attachedTagId], $image->getTagIds());
    self::assertSame(License::CC_BY, $image->getLicense());
  }

  #[Test]
  public function everyWriteOnAnImageSucceeds(): void {
    $this->bootActors();

    $imageId = $this->uploadTaggedLicensedImage(self::DESCRIPTION);
    self::assertWriteSucceeded($this->performWrite('addToCollection', $imageId));
    self::assertWriteSucceeded($this->performWrite('untag', $imageId));
    self::assertSame(200, $this->viewImage($imageId));
    self::assertWriteSucceeded($this->deleteImage($imageId));

    self::assertSame(0, $this->countRowsByColumn('image', 'uuid', $imageId));
  }

  #[Test]
  public function storingDispatchesDecoratedMessages(): void {
    $dispatcher = new RecordingMessageDispatcher();
    static::getContainer()->set(EventDispatcher::class, $dispatcher);
    $imageId = ID::generate();

    $this->imageStore()->store(Image::create(
      $imageId,
      ID::generate(),
      ImageAttributes::create('decorated.png', 'Decorated', true),
      new ImageMetadata(1024, 'image/png', 2, 2),
    ));

    self::assertCount(1, $dispatcher->messages);
    $message = $dispatcher->messages[0];
    self::assertInstanceOf(ImageWasCreated::class, $message->payload());
    self::assertSame($imageId->toString(), $message->aggregateRootId()?->toString());
    self::assertSame('slink.image.domain.event.image_was_created', $message->header(Header::EVENT_TYPE));
    self::assertSame('slink.image.domain.image', $message->header(Header::AGGREGATE_ROOT_TYPE));
    self::assertNotNull($message->header(Header::AGGREGATE_ROOT_VERSION));
    self::assertNotNull($message->header(Header::TIME_OF_RECORDING));
  }

  #[Test]
  public function viewingAnImageCountsTheViewAndLeavesItsDescriptionUnchanged(): void {
    $id = $this->createAggregate();
    $description = $this->detailDescription($id->toString());

    self::assertSame([200, 200], [$this->viewImage($id->toString()), $this->viewImage($id->toString())]);

    self::assertSame(2, (int) $this->imageColumn($id->toString(), 'views'));
    self::assertSame(2, $this->reload($id)->getAttributes()->getViews());
    self::assertSame(self::STORED_DESCRIPTION, $this->imageColumn($id->toString(), 'description'));
    self::assertSame($description, $this->detailDescription($id->toString()));
  }

  #[Test]
  public function updatingAReloadedImageKeepsItsDescriptionEscapedOnce(): void {
    $id = $this->createAggregate();
    $description = $this->detailDescription($id->toString());

    self::assertWriteSucceeded($this->updateImage($id->toString(), ['description' => null, 'isPublic' => false]));

    self::assertSame(self::STORED_DESCRIPTION, $this->imageColumn($id->toString(), 'description'));
    self::assertSame($description, $this->detailDescription($id->toString()));
    self::assertSame([$this->attachedTagId], $this->reload($id)->getTagIds());
    self::assertSame(License::CC_BY, $this->reload($id)->getLicense());
  }

  #[Test]
  public function anImageWithoutALicenseRowReloadsWithoutALicense(): void {
    $id = $this->createAggregate();
    $this->readModel()->executeStatement('DELETE FROM image_license WHERE image_id = ?', [$id->toString()]);

    self::assertNull($this->reload($id)->getLicense());
  }

  #[Test]
  public function aGuestUploadReloadsWithNoOwner(): void {
    $this->setAccessSettings(['allowGuestUploads' => true]);
    $this->client->request('POST', '/api/upload', ['description' => self::DESCRIPTION], ['image' => $this->sampleImage()]);
    self::assertContains($this->client->getResponse()->getStatusCode(), [200, 201]);
    $id = ID::fromString((string) $this->readModel()->fetchOne('SELECT uuid FROM "image" WHERE user_id IS NULL'));

    $image = $this->reload($id);

    self::assertNull($image->getUserId());
    self::assertSame($this->storedState($id), $this->aggregateState($image));
  }

  #[Test]
  public function fetchingAnUnknownImageThrowsNotFound(): void {
    $this->expectException(NotFoundException::class);

    $this->imageStore()->get(ID::fromString(self::UNKNOWN_IMAGE_ID));
  }

  #[Test]
  #[DataProvider('singleImageWrites')]
  public function writingAnUnknownImageAnswersNotFound(string $write): void {
    $this->bootActors();

    self::assertSame(404, $this->performWrite($write, self::UNKNOWN_IMAGE_ID));
  }

  #[Test]
  #[DataProvider('singleImageWrites')]
  public function writingADeletedImageAnswersNotFound(string $write): void {
    $this->bootActors();
    $imageId = $this->uploadDescribedImage('short-lived');
    self::assertWriteSucceeded($this->tagImage($imageId, $this->attachedTagId));
    self::assertWriteSucceeded($this->deleteImage($imageId));

    self::assertSame(404, $this->performWrite($write, $imageId));
  }

  #[Test]
  public function batchDeleteReportsAnUnknownImageAndDeletesTheRest(): void {
    $this->bootActors();
    $imageId = $this->uploadDescribedImage('batched');

    $status = $this->send('DELETE', '/api/images/batch-delete', $this->ownerToken, [
      'imageIds' => [$imageId, self::UNKNOWN_IMAGE_ID],
    ]);

    self::assertSame(200, $status);
    self::assertSame([self::UNKNOWN_IMAGE_ID], $this->failedIds());
    self::assertSame(0, $this->countRowsByColumn('image', 'uuid', $imageId));
  }

  #[Test]
  public function batchVisibilityUpdateReportsAnUnknownImageAndUpdatesTheRest(): void {
    $this->bootActors();
    $imageId = $this->uploadDescribedImage('batched');

    $status = $this->send('PATCH', '/api/images/batch', $this->ownerToken, [
      'imageIds' => [$imageId, self::UNKNOWN_IMAGE_ID],
      'isPublic' => true,
    ]);

    self::assertSame(200, $status);
    self::assertSame([self::UNKNOWN_IMAGE_ID], $this->failedIds());
    self::assertTrue((bool) $this->imageColumn($imageId, 'is_public'));
  }

  #[Test]
  public function batchReassignReportsAnUnknownImageAndReassignsTheRest(): void {
    $this->bootActors();
    $imageId = $this->uploadDescribedImage('batched');

    $status = $this->send('PUT', '/api/images/batch', $this->ownerToken, [
      'assignments' => [
        $imageId => ['tagIds' => [$this->freeTagId]],
        self::UNKNOWN_IMAGE_ID => ['tagIds' => [$this->freeTagId]],
      ],
    ]);

    self::assertSame(200, $status);
    self::assertSame([self::UNKNOWN_IMAGE_ID], $this->failedIds());
    self::assertSame([$this->freeTagId], $this->reload(ID::fromString($imageId))->getTagIds());
  }

  #[Test]
  public function purgingAUserRemovesTheirImagesThroughTheStore(): void {
    $this->bootActors();
    $firstImageId = $this->uploadTaggedLicensedImage(self::DESCRIPTION);
    $secondImageId = $this->uploadDescribedImage('second');
    self::assertSame(2, $this->countRowsByColumn('image', 'user_id', $this->ownerId));

    $this->softDelete($this->adminToken, $this->ownerId);
    self::assertSame(204, $this->send('DELETE', '/api/user/' . $this->ownerId, $this->adminToken));

    self::assertSame(0, $this->countRowsByColumn('image', 'user_id', $this->ownerId));
    self::assertSame(0, $this->countRowsByColumn('image_license', 'image_id', $firstImageId));
  }

  #[Test]
  #[Group('sc-1203')]
  public function bothOfTwoConcurrentViewsCount(): void {
    $id = $this->createAggregate();
    $firstContainer = $this->independentContainer();
    $secondContainer = $this->independentContainer();
    $this->imageStoreOf($firstContainer)->get($id);
    $this->imageStoreOf($secondContainer)->get($id);

    $this->countViewIn($firstContainer, $id);
    $this->countViewIn($secondContainer, $id);

    self::assertSame(2, (int) $this->imageColumn($id->toString(), 'views'));
  }

  private function countViewIn(ContainerInterface $container, ID $id): void {
    /** @var CommandBusInterface $commandBus */
    $commandBus = $container->get(CommandBusInterface::class);
    $commandBus->handle(new AddImageViewCountCommand($id->toString()));
  }

  /**
   * @return list<string>
   */
  private function failedIds(): array {
    /** @var array{data?: array{failed?: list<array{id: string}>}, failed?: list<array{id: string}>} $payload */
    $payload = $this->responsePayload();
    $failed = $payload['data']['failed'] ?? $payload['failed'] ?? [];

    return \array_column($failed, 'id');
  }

  private function storedLicense(ID $id): ?string {
    $license = $this->readModel()->fetchOne('SELECT license FROM image_license WHERE image_id = ?', [$id->toString()]);

    if ($license === false) {
      return null;
    }

    return (string) $license;
  }

  private function imageStore(): ImageStoreRepositoryInterface {
    return $this->imageStoreOf(static::getContainer());
  }

  private function imageStoreOf(ContainerInterface $container): ImageStoreRepositoryInterface {
    /** @var ImageStoreRepositoryInterface $store */
    $store = $container->get(ImageStoreRepositoryInterface::class);

    return $store;
  }
}
