<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Share;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Share\Domain\Repository\ShareStoreRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Tests\Integration\Console\Boot\RollsBackToPreviousRelease;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1177')]
final class ShareStoreUpgradeTest extends HttpTestCase {
  use DrivesShareWrites;
  use RollsBackToPreviousRelease;

  private string $ownerId = '';
  private string $ownerToken = '';
  private string $imageId = '';
  private string $shareId = '';
  private string $collectionId = '';
  private string $collectionShareId = '';

  protected function setUp(): void {
    parent::setUp();

    $this->ownerId = $this->createUser('share-upgrade-owner@local.test', 'shareupgradeowner', self::PASSWORD);
    $this->ownerToken = $this->login('shareupgradeowner', self::PASSWORD);
    $this->imageId = $this->uploadImage($this->ownerToken, false);
    $this->shareId = $this->createImageShare($this->ownerToken, $this->imageId);
    $this->setSharePassword($this->ownerToken, $this->shareId, self::SHARE_PASSWORD);
    $this->setShareExpiration($this->ownerToken, $this->shareId, $this->futureIso(3600));
    $this->collectionId = $this->createCollection($this->ownerToken);
    $this->collectionShareId = $this->createCollectionShare($this->ownerToken, $this->collectionId);

    $this->rollBackToPreviousRelease();
    $this->bootApplicationOnPreviousReleaseDatabase();
  }

  protected function tearDown(): void {
    $this->restorePreviousReleaseBackup();

    parent::tearDown();
  }

  #[Test]
  public function aShareFromThePreviousReleaseReloadsFromItsRows(): void {
    $stored = $this->connection()->fetchAssociative('SELECT * FROM "share" WHERE uuid = ?', [$this->shareId]);
    self::assertIsArray($stored);

    $this->forgetLoadedRows();
    $share = $this->shareStore()->get(ID::fromString($this->shareId));

    self::assertSame($this->imageId, $share->getShareableId());
    self::assertSame($stored['target_url'], $share->getTargetPath()->toString());
    self::assertSame($stored['password_hash'], $share->getAccessControl()->passwordHash);
    self::assertSame($this->shortCodeOf($this->shareId), $share->getShortCode());
    self::assertNotNull($share->getExpiresAt());
    self::assertFalse($share->isPublished());
  }

  #[Test]
  #[DataProvider('shareWrites')]
  public function aWriteOnAShareFromThePreviousReleaseSucceeds(string $write): void {
    self::assertSame(204, $this->writeShare($write, $this->shareId, $this->ownerToken));
  }

  #[Test]
  public function aWrongPasswordIsStillRefusedOnAShareFromThePreviousRelease(): void {
    $this->forgetLoadedRows();

    [$status] = $this->unlockShare($this->shareId, 'not-the-password');

    self::assertSame(400, $status);
  }

  #[Test]
  public function sharingAnImageAgainReturnsItsShareFromThePreviousRelease(): void {
    self::assertSame($this->shareId, $this->shareImage($this->imageId, $this->ownerToken));
    self::assertSame(1, $this->countRowsByColumn('share', 'shareable_id', $this->imageId));
  }

  #[Test]
  public function aPublishedShareFromThePreviousReleaseKeepsItsShortUrlAndListing(): void {
    self::assertSame(204, $this->writeShare('publish', $this->shareId, $this->ownerToken));

    self::assertSame(200, $this->apiRequest('GET', '/api/shares', $this->ownerToken));
    self::assertStringContainsString($this->shareId, (string) $this->client->getResponse()->getContent());
    self::assertSame(302, $this->apiRequest('GET', \sprintf('/i/%s', (string) $this->shortCodeOf($this->shareId))));
  }

  #[Test]
  public function deletingAnImageRevokesItsShareFromThePreviousRelease(): void {
    $this->forgetLoadedRows();

    self::assertSame(204, $this->apiRequest('DELETE', \sprintf('/api/image/%s', $this->imageId), $this->ownerToken));

    self::assertSame(0, $this->countRowsByColumn('share', 'uuid', $this->shareId));
    self::assertSame(0, $this->countRowsByColumn('short_url', 'share_id', $this->shareId));
  }

  #[Test]
  public function deletingACollectionRevokesItsShareFromThePreviousRelease(): void {
    $this->forgetLoadedRows();

    self::assertSame(204, $this->apiRequest(
      'DELETE',
      '/api/collection',
      $this->ownerToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $this->collectionId], JSON_THROW_ON_ERROR),
    ));

    self::assertSame(0, $this->countRowsByColumn('share', 'uuid', $this->collectionShareId));
  }

  #[Test]
  public function sharesKeepWorkingBesideTheOtherStoresAfterBoot(): void {
    $this->forgetLoadedRows();
    self::assertSame(204, $this->writeShare('publish', $this->shareId, $this->ownerToken));

    $this->forgetLoadedRows();
    self::assertContains(
      $this->apiRequest(
        'PATCH',
        '/api/collection',
        $this->ownerToken,
        ['CONTENT_TYPE' => 'application/json'],
        \json_encode(['id' => $this->collectionId, 'name' => 'renamed after upgrade', 'description' => ''], JSON_THROW_ON_ERROR),
      ),
      [200, 204],
    );
    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/image/%s/detail', $this->imageId), $this->ownerToken));

    $this->forgetLoadedRows();
    self::assertSame(
      'renamed after upgrade',
      $this->connection()->fetchOne('SELECT name FROM "collection" WHERE uuid = ?', [$this->collectionId]),
    );
    self::assertSame($this->ownerId, $this->userStore()->get(ID::fromString($this->ownerId))->aggregateRootId()->toString());
    self::assertTrue($this->shareStore()->get(ID::fromString($this->shareId))->isPublished());
  }

  private function shareStore(): ShareStoreRepositoryInterface {
    /** @var ShareStoreRepositoryInterface $store */
    $store = static::getContainer()->get(ShareStoreRepositoryInterface::class);

    return $store;
  }

  private function userStore(): UserStoreRepositoryInterface {
    /** @var UserStoreRepositoryInterface $store */
    $store = static::getContainer()->get(UserStoreRepositoryInterface::class);

    return $store;
  }

  private function shortCodeOf(string $shareId): mixed {
    return $this->connection()->fetchOne('SELECT short_code FROM "short_url" WHERE share_id = ?', [$shareId]);
  }
}
