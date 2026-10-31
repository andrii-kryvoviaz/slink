<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Bookmark;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations\Version20261031202314;
use Tests\Integration\Console\Boot\RollsBackToPreviousRelease;
use Tests\Integration\Http\HttpTestCase;

final class BookmarkCountColumnUpgradeTest extends HttpTestCase {
  use RollsBackToPreviousRelease;

  private const int DRIFTED_COUNT = 9;

  private string $ownerToken = '';
  private string $firstReaderToken = '';
  private string $popularImageId = '';
  private string $lonelyImageId = '';

  protected function setUp(): void {
    parent::setUp();

    $this->createUser('upgrade-count-owner@local.test', 'upgradecountowner', self::PASSWORD);
    $this->createUser('upgrade-count-first@local.test', 'upgradecountfirst', self::PASSWORD);
    $this->createUser('upgrade-count-second@local.test', 'upgradecountsecond', self::PASSWORD);
    $this->ownerToken = $this->login('upgradecountowner', self::PASSWORD);
    $this->firstReaderToken = $this->login('upgradecountfirst', self::PASSWORD);
    $secondReaderToken = $this->login('upgradecountsecond', self::PASSWORD);
    $this->popularImageId = $this->uploadImage($this->ownerToken, true);
    $this->lonelyImageId = $this->uploadImage($this->ownerToken, true);
    $this->bookmark($this->firstReaderToken, $this->popularImageId);
    $this->bookmark($secondReaderToken, $this->popularImageId);

    $this->rollBackToPreviousRelease();
    $this->connection()->executeStatement('UPDATE "image" SET bookmark_count = ?', [self::DRIFTED_COUNT]);
    $this->bootApplicationOnPreviousReleaseDatabase();
  }

  protected function tearDown(): void {
    $this->restorePreviousReleaseBackup();

    parent::tearDown();
  }

  private function bookmark(string $token, string $imageId): void {
    self::assertSame(201, $this->apiRequest('POST', \sprintf('/api/image/%s/bookmark', $imageId), $token));
  }

  private function forgetLoadedRows(): void {
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);
    self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
    $entityManager->clear();
  }

  /**
   * @return list<string>
   */
  private function imageColumns(): array {
    /** @var list<string> $columns */
    $columns = $this->connection()->fetchFirstColumn("SELECT name FROM pragma_table_info('image')");

    return $columns;
  }

  /**
   * @return array<string, mixed>
   */
  private function listedCounts(): array {
    $this->forgetLoadedRows();
    self::assertSame(200, $this->apiRequest('GET', '/api/images', $this->firstReaderToken));

    /** @var array{data: list<array{id: string, bookmarkCount: int}>} $payload */
    $payload = $this->responsePayload();

    return \array_column($payload['data'], 'bookmarkCount', 'id');
  }

  #[Test]
  public function theUpgradeDropsTheStoredCountColumnAndKeepsTheOtherColumns(): void {
    $columns = $this->imageColumns();

    self::assertNotContains('bookmark_count', $columns);
    self::assertContains('views', $columns);
    self::assertContains('version', $columns);
  }

  #[Test]
  public function afterTheUpgradeEveryCountIsDerivedFromTheBookmarkRowsNotTheDroppedColumn(): void {
    self::assertEquals([$this->popularImageId => 2, $this->lonelyImageId => 0], $this->listedCounts());
  }

  #[Test]
  public function afterTheUpgradeBookmarksStillAddAndRemoveAndTheCountFollows(): void {
    $this->bookmark($this->firstReaderToken, $this->lonelyImageId);

    self::assertEquals([$this->popularImageId => 2, $this->lonelyImageId => 1], $this->listedCounts());

    self::assertContains(
      $this->apiRequest('DELETE', \sprintf('/api/image/%s/bookmark', $this->popularImageId), $this->firstReaderToken),
      [200, 204],
    );

    self::assertEquals([$this->popularImageId => 1, $this->lonelyImageId => 1], $this->listedCounts());
  }

  #[Test]
  public function theMigrationReverts(): void {
    [$status, $output] = $this->consoleResult([
      'command' => 'doctrine:migrations:execute',
      'versions' => [Version20261031202314::class],
      '--down' => true,
    ]);

    self::assertSame(0, $status, $output);
    self::assertContains('bookmark_count', $this->imageColumns());
    self::assertEquals(
      [$this->popularImageId => 2, $this->lonelyImageId => 0],
      $this->connection()->fetchAllKeyValue('SELECT uuid, bookmark_count FROM "image"'),
    );
  }
}
