<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Image;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Image\Application\Command\CalculateImageHash\CalculateImageHashCommand;
use Slink\Image\Domain\Enum\License;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Tests\Integration\Console\Boot\RollsBackToPreviousRelease;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1179')]
final class ImageStoreUpgradeTest extends HttpTestCase {
  use DrivesImageWrites;
  use RollsBackToPreviousRelease;

  private const string DESCRIPTION = 'Tom & Jerry <b>"quoted"</b>';
  private const string STORED_DESCRIPTION = 'Tom &amp; Jerry &lt;b&gt;&quot;quoted&quot;&lt;/b&gt;';

  private string $imageId = '';

  protected function setUp(): void {
    parent::setUp();

    $this->bootActors();
    $this->imageId = $this->uploadTaggedLicensedImage(self::DESCRIPTION);

    $this->rollBackToPreviousRelease();
    $this->bootApplicationOnPreviousReleaseDatabase();
  }

  protected function tearDown(): void {
    $this->restorePreviousReleaseBackup();

    parent::tearDown();
  }

  #[Test]
  public function anImageFromThePreviousReleaseReloadsFromItsRows(): void {
    $this->forgetImageStream();
    $this->forgetLoadedRows();

    $image = $this->imageStore()->get(ID::fromString($this->imageId));

    self::assertSame($this->ownerId, $image->getUserId()?->toString());
    self::assertSame(self::STORED_DESCRIPTION, $image->getAttributes()->getDescription());
    self::assertSame([$this->attachedTagId], $image->getTagIds());
    self::assertSame(License::CC_BY, $image->getLicense());
  }

  #[Test]
  #[DataProvider('singleImageWrites')]
  public function aWriteOnAnImageFromThePreviousReleaseSucceedsAndAppendsNothing(string $write): void {
    $this->forgetImageStream();

    self::assertWriteSucceeded($this->performWrite($write, $this->imageId));

    self::assertSame(0, $this->imageEvents());
  }

  #[Test]
  public function aViewOfAnImageFromThePreviousReleaseCountsAndKeepsTheDescriptionEscapedOnce(): void {
    $this->forgetImageStream();

    self::assertSame(200, $this->viewImage($this->imageId));

    self::assertSame(1, (int) $this->imageColumn($this->imageId, 'views'));
    self::assertSame(self::STORED_DESCRIPTION, $this->imageColumn($this->imageId, 'description'));
  }

  #[Test]
  public function anUpdateOfAnImageFromThePreviousReleaseKeepsTheDescriptionEscapedOnce(): void {
    $this->forgetImageStream();

    self::assertWriteSucceeded($this->updateImage($this->imageId, ['description' => null, 'isPublic' => false]));

    self::assertSame(self::STORED_DESCRIPTION, $this->imageColumn($this->imageId, 'description'));
    self::assertFalse((bool) $this->imageColumn($this->imageId, 'is_public'));
  }

  #[Test]
  public function batchDeleteRemovesAnImageFromThePreviousRelease(): void {
    $this->forgetImageStream();

    self::assertSame(200, $this->send('DELETE', '/api/images/batch-delete', $this->ownerToken, [
      'imageIds' => [$this->imageId],
    ]));

    self::assertSame(0, $this->countRowsByColumn('image', 'uuid', $this->imageId));
  }

  #[Test]
  public function batchVisibilityUpdateChangesAnImageFromThePreviousRelease(): void {
    $this->forgetImageStream();

    self::assertSame(200, $this->send('PATCH', '/api/images/batch', $this->ownerToken, [
      'imageIds' => [$this->imageId],
      'isPublic' => false,
    ]));

    self::assertFalse((bool) $this->imageColumn($this->imageId, 'is_public'));
  }

  #[Test]
  public function batchReassignRetagsAnImageFromThePreviousRelease(): void {
    $this->forgetImageStream();

    self::assertSame(200, $this->send('PUT', '/api/images/batch', $this->ownerToken, [
      'assignments' => [$this->imageId => ['tagIds' => [$this->freeTagId]]],
    ]));

    self::assertSame(
      [$this->freeTagId],
      $this->doctrine()->getConnection()->fetchFirstColumn(
        'SELECT tag_id FROM image_to_tag WHERE image_id = ?',
        [$this->imageId],
      ),
    );
  }

  #[Test]
  public function aDefaultLicenseSyncReachesAnImageFromThePreviousRelease(): void {
    $this->forgetImageStream();

    self::assertWriteSucceeded($this->send('PATCH', '/api/user/preferences', $this->ownerToken, [
      'license.default' => 'cc0',
      'license.syncToImages' => true,
    ]));

    self::assertSame(
      'cc0',
      $this->doctrine()->getConnection()->fetchOne('SELECT license FROM image_license WHERE image_id = ?', [$this->imageId]),
    );
  }

  #[Test]
  public function aMissingHashIsCalculatedForAnImageFromThePreviousRelease(): void {
    $this->forgetImageStream();
    $this->doctrine()->getConnection()->executeStatement('UPDATE "image" SET sha1_hash = NULL WHERE uuid = ?', [$this->imageId]);
    $this->forgetLoadedRows();

    $this->commandBus()->handleSync(new CalculateImageHashCommand(ID::fromString($this->imageId)));

    self::assertSame(40, \strlen((string) $this->imageColumn($this->imageId, 'sha1_hash')));
  }

  #[Test]
  public function purgingAUserRemovesAnImageFromThePreviousRelease(): void {
    $this->forgetImageStream();

    $this->softDelete($this->adminToken, $this->ownerId);
    self::assertSame(204, $this->send('DELETE', '/api/user/' . $this->ownerId, $this->adminToken));

    self::assertSame(0, $this->countRowsByColumn('image', 'user_id', $this->ownerId));
  }

  #[Test]
  public function imagesKeepWorkingBesideTheOtherStoresAfterBoot(): void {
    $imageId = $this->uploadTaggedLicensedImage('side by side');

    self::assertWriteSucceeded($this->performWrite('addToCollection', $imageId));
    self::assertNotSame('', $this->createImageShare($this->ownerToken, $imageId));
    self::assertNotSame('', $this->postComment($this->adminToken, $imageId, 'nice'));
    self::assertWriteSucceeded($this->send('POST', \sprintf('/api/image/%s/bookmark', $imageId), $this->adminToken));
    self::assertSame(200, $this->send('GET', '/api/images', $this->ownerToken));
    self::assertStringContainsString($imageId, (string) $this->client->getResponse()->getContent());

    self::assertSame(1, $this->countRowsByColumn('collection_item', 'item_id', $imageId));
    self::assertSame(1, $this->countRowsByColumn('bookmark', 'image_id', $imageId));
    self::assertSame(0, $this->eventsOf($imageId));
  }

  private function imageStore(): ImageStoreRepositoryInterface {
    /** @var ImageStoreRepositoryInterface $store */
    $store = static::getContainer()->get(ImageStoreRepositoryInterface::class);

    return $store;
  }

  private function imageEvents(): int {
    return $this->eventsOf($this->imageId);
  }

  private function eventsOf(string $aggregateId): int {
    return (int) $this->eventStore()->fetchOne(
      'SELECT COUNT(*) FROM event_store WHERE aggregate_root_id = ?',
      [$aggregateId],
    );
  }

  private function forgetImageStream(): void {
    $this->eventStore()->executeStatement('DELETE FROM event_store WHERE aggregate_root_id = ?', [$this->imageId]);
    $this->eventStore()->executeStatement('DELETE FROM snapshots WHERE aggregate_root_id = ?', [$this->imageId]);
  }

  private function eventStore(): Connection {
    /** @var Connection $eventStore */
    $eventStore = static::getContainer()->get('doctrine.dbal.event_store_connection');

    return $eventStore;
  }
}
