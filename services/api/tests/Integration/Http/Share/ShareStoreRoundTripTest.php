<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Share;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use EventSauce\EventSourcing\Header;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Share\Application\Command\RevokeShare\RevokeShareCommand;
use Slink\Share\Domain\Event\ShareWasCreated;
use Slink\Share\Domain\Repository\ShareStoreRepositoryInterface;
use Slink\Share\Domain\Share;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Domain\ValueObject\ShareContext;
use Slink\Share\Domain\ValueObject\TargetPath;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\MessageBus\Event\EventDispatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tests\Integration\Http\Double\RecordingMessageDispatcher;
use Tests\Integration\Http\Store\ViewBackedStoreRoundTripTestCase;

/**
 * @extends ViewBackedStoreRoundTripTestCase<Share>
 */
#[Group('sc-1177')]
final class ShareStoreRoundTripTest extends ViewBackedStoreRoundTripTestCase {
  use DrivesShareWrites;

  private const string TIMESTAMP_FORMAT = 'Y-m-d H:i:s';
  private const string UNKNOWN_SHARE_ID = '11111111-2222-4333-8444-555555555555';
  private const int SHORT_CODE_LENGTH = 8;
  private const int LONGER_SHORT_CODE_LENGTH = 12;

  private string $ownerToken = '';

  #[\Override]
  protected function createAggregate(): ID {
    $this->bootOwner();
    $this->configureShortening(true, self::SHORT_CODE_LENGTH);
    $shareId = $this->createImageShare($this->ownerToken, $this->uploadImage($this->ownerToken, false));
    $this->setSharePassword($this->ownerToken, $shareId, self::SHARE_PASSWORD);
    $this->setShareExpiration($this->ownerToken, $shareId, $this->futureIso(3600));
    $this->publishShare($this->ownerToken, $shareId);

    return ID::fromString($shareId);
  }

  #[\Override]
  protected function fetch(ID $id): Share {
    return $this->shareStore()->get($id);
  }

  #[\Override]
  protected function aggregateState(object $aggregate): array {
    $accessControl = $aggregate->getAccessControl();
    $context = $aggregate->getContext();

    return [
      'shareableId' => $aggregate->getShareable()->getShareableId(),
      'shareableType' => $aggregate->getShareable()->getShareableType()->value,
      'targetPath' => $aggregate->getTargetPath()->toString(),
      'createdAt' => $aggregate->getCreatedAt()->format(self::TIMESTAMP_FORMAT),
      'shortUrlId' => $context->getShortUrlId()?->toString(),
      'shortCode' => $context->getShortCode(),
      'isPublished' => $accessControl->isPublished,
      'expiresAt' => $accessControl->expiresAt?->format(self::TIMESTAMP_FORMAT),
      'passwordHash' => $accessControl->passwordHash,
    ];
  }

  #[\Override]
  protected function storedState(ID $id): array {
    $share = $this->readModel()->fetchAssociative('SELECT * FROM "share" WHERE uuid = ?', [$id->toString()]);
    $shortUrl = $this->shortUrlOf($id->toString());
    self::assertIsArray($share);
    self::assertIsArray($shortUrl);

    return [
      'shareableId' => $share['shareable_id'],
      'shareableType' => $share['shareable_type'],
      'targetPath' => $share['target_url'],
      'createdAt' => $this->secondsOf($share['created_at']),
      'shortUrlId' => $shortUrl['uuid'],
      'shortCode' => $shortUrl['short_code'],
      'isPublished' => (bool) $share['is_published'],
      'expiresAt' => $this->secondsOf($share['expires_at']),
      'passwordHash' => $share['password_hash'],
    ];
  }

  #[\Override]
  protected function rootTable(): string {
    return 'share';
  }

  #[Test]
  public function aShareBuiltThroughCommandsHoldsItsStateInRows(): void {
    $shareId = $this->createAggregate();

    $stored = $this->storedState($shareId);
    self::assertTrue($stored['isPublished']);
    self::assertNotNull($stored['expiresAt']);
    self::assertNotNull($stored['passwordHash']);
    self::assertSame(self::SHORT_CODE_LENGTH, \strlen((string) $stored['shortCode']));
  }

  #[Test]
  public function aReloadedShareTakesItsWholeStateFromTheRows(): void {
    $shareId = $this->createAggregate();
    $this->readModel()->executeStatement(
      <<<'SQL'
        UPDATE "share"
        SET shareable_id = ?, target_url = ?, created_at = ?, is_published = 0, expires_at = ?, password_hash = ?
        WHERE uuid = ?
        SQL,
      [
        '99999999-8888-4777-8666-555555555555',
        '/image/relocated.png',
        '2021-02-03 04:05:06',
        '2031-04-05 06:07:08',
        'stored-hash',
        $shareId->toString(),
      ],
    );
    $this->readModel()->executeStatement(
      'UPDATE "short_url" SET uuid = ?, short_code = ? WHERE share_id = ?',
      ['aaaaaaaa-bbbb-4ccc-8ddd-eeeeeeeeeeee', 'ROWCODE1', $shareId->toString()],
    );

    self::assertSame(
      [
        'shareableId' => '99999999-8888-4777-8666-555555555555',
        'shareableType' => 'image',
        'targetPath' => '/image/relocated.png',
        'createdAt' => '2021-02-03 04:05:06',
        'shortUrlId' => 'aaaaaaaa-bbbb-4ccc-8ddd-eeeeeeeeeeee',
        'shortCode' => 'ROWCODE1',
        'isPublished' => false,
        'expiresAt' => '2031-04-05 06:07:08',
        'passwordHash' => 'stored-hash',
      ],
      $this->aggregateState($this->reload($shareId)),
    );
  }

  #[Test]
  public function aShareWithoutAShortUrlRowReloadsWithAnEmptyContext(): void {
    $shareId = $this->createAggregate();
    $shareableId = $this->storedState($shareId)['shareableId'];
    $this->readModel()->executeStatement('DELETE FROM "short_url" WHERE share_id = ?', [$shareId->toString()]);

    $share = $this->reload($shareId);

    self::assertFalse($share->getContext()->hasShortUrl());
    self::assertNull($share->getContext()->getShortCode());
    self::assertNull($share->getContext()->getShortUrlId());
    self::assertNull($share->getShortCode());
    self::assertSame($shareableId, $share->getContext()->getShareable()->getShareableId());
    self::assertFalse($share->hasStaleShortCode(self::SHORT_CODE_LENGTH));
  }

  #[Test]
  public function aShareCreatedWithoutShorteningReloadsWithAnEmptyContext(): void {
    $this->bootOwner();
    $this->configureShortening(false, self::SHORT_CODE_LENGTH);
    $shareId = $this->createImageShare($this->ownerToken, $this->uploadImage($this->ownerToken, false));

    self::assertFalse($this->shortUrlOf($shareId));
    self::assertFalse($this->reload(ID::fromString($shareId))->getContext()->hasShortUrl());
  }

  #[Test]
  public function aStaleShortCodeInTheRowIsDetectedAfterReload(): void {
    $shareId = $this->createUnpublishedShare();
    $this->storeShortCode($shareId, 'STALE-CODE-12');

    $share = $this->reload(ID::fromString($shareId));

    self::assertTrue($share->hasStaleShortCode(self::SHORT_CODE_LENGTH));
    self::assertFalse($share->hasStaleShortCode(\strlen('STALE-CODE-12')));
  }

  #[Test]
  public function aPublishedRowIsNeverStaleAfterReload(): void {
    $shareId = $this->createUnpublishedShare();
    $this->readModel()->executeStatement('UPDATE "share" SET is_published = 1 WHERE uuid = ?', [$shareId]);

    self::assertFalse($this->reload(ID::fromString($shareId))->hasStaleShortCode(self::LONGER_SHORT_CODE_LENGTH));
  }

  #[Test]
  public function sharingAgainRegeneratesAStaleShortCodeOnTheSameShortUrl(): void {
    $this->bootOwner();
    $this->configureShortening(true, self::SHORT_CODE_LENGTH);
    $imageId = $this->uploadImage($this->ownerToken, false);
    $shareId = $this->shareImage($imageId, $this->ownerToken);
    $shortUrlBefore = $this->shortUrlOf($shareId);
    self::assertIsArray($shortUrlBefore);
    $this->configureShortening(true, self::LONGER_SHORT_CODE_LENGTH);

    self::assertSame($shareId, $this->shareImage($imageId, $this->ownerToken));

    $shortUrlAfter = $this->shortUrlOf($shareId);
    self::assertIsArray($shortUrlAfter);
    self::assertSame($shortUrlBefore['uuid'], $shortUrlAfter['uuid']);
    self::assertSame(self::LONGER_SHORT_CODE_LENGTH, \strlen((string) $shortUrlAfter['short_code']));
  }

  #[Test]
  public function sharingAgainKeepsTheShortCodeOfAPublishedShare(): void {
    $this->bootOwner();
    $this->configureShortening(true, self::SHORT_CODE_LENGTH);
    $imageId = $this->uploadImage($this->ownerToken, false);
    $shareId = $this->shareImage($imageId, $this->ownerToken);
    $this->publishShare($this->ownerToken, $shareId);
    $shortUrlBefore = $this->shortUrlOf($shareId);
    $this->configureShortening(true, self::LONGER_SHORT_CODE_LENGTH);

    self::assertSame($shareId, $this->shareImage($imageId, $this->ownerToken));

    self::assertSame($shortUrlBefore, $this->shortUrlOf($shareId));
  }

  #[Test]
  public function sharingAgainWithShorteningEnabledGivesAShareWithoutAShortUrlItsFirstOne(): void {
    $this->bootOwner();
    $this->configureShortening(false, self::SHORT_CODE_LENGTH);
    $imageId = $this->uploadImage($this->ownerToken, false);
    $shareId = $this->shareImage($imageId, $this->ownerToken);
    self::assertFalse($this->shortUrlOf($shareId));
    $this->configureShortening(true, self::SHORT_CODE_LENGTH);

    self::assertSame($shareId, $this->shareImage($imageId, $this->ownerToken));

    self::assertIsArray($this->shortUrlOf($shareId));
    self::assertTrue($this->reload(ID::fromString($shareId))->getContext()->hasShortUrl());
  }

  #[Test]
  public function repeatingTheSameExpirationLeavesTheRowUntouched(): void {
    $this->bootOwner();
    $shareId = $this->createImageShare($this->ownerToken, $this->uploadImage($this->ownerToken, false));
    $expiresAt = $this->futureIso(3600);
    $this->setShareExpiration($this->ownerToken, $shareId, $expiresAt);
    $versionBefore = $this->shareVersion($shareId);
    $this->forgetLoadedRows();

    $this->setShareExpiration($this->ownerToken, $shareId, $expiresAt);

    self::assertSame($versionBefore, $this->shareVersion($shareId));
  }

  #[Test]
  public function revokingAShareRemovesItsRows(): void {
    $shareId = $this->createAggregate();

    $this->revoke($shareId);

    self::assertSame(0, $this->countRowsByColumn('share', 'uuid', $shareId->toString()));
    self::assertSame(0, $this->countRowsByColumn('short_url', 'share_id', $shareId->toString()));
  }

  #[Test]
  public function loadingARevokedShareThrowsNotFound(): void {
    $shareId = $this->createAggregate();
    $this->revoke($shareId);

    $this->expectException(NotFoundException::class);

    $this->reload($shareId);
  }

  #[Test]
  public function loadingAnUnknownShareThrowsNotFound(): void {
    $this->expectException(NotFoundException::class);

    $this->reload(ID::fromString(self::UNKNOWN_SHARE_ID));
  }

  #[Test]
  public function findingByTargetPathLoadsTheShareFromItsRows(): void {
    $shareId = $this->createAggregate();
    $this->readModel()->executeStatement(
      'UPDATE "share" SET target_url = ? WHERE uuid = ?',
      ['/image/relocated.png', $shareId->toString()],
    );
    $this->entityManager()->clear();

    $share = $this->shareStore()->findByTargetPath(TargetPath::fromString('/image/relocated.png'));

    self::assertNotNull($share);
    self::assertSame($shareId->toString(), $share->getId());
    self::assertSame('/image/relocated.png', $share->getTargetPath()->toString());
  }

  #[Test]
  public function findingAnUnknownTargetPathFindsNothing(): void {
    self::assertNull($this->shareStore()->findByTargetPath(TargetPath::fromString('/image/never-shared.png')));
  }

  #[Test]
  public function findingTheTargetPathOfARevokedShareFindsNothing(): void {
    $shareId = $this->createAggregate();
    $targetPath = (string) $this->storedState($shareId)['targetPath'];
    $this->revoke($shareId);
    $this->entityManager()->clear();

    self::assertNull($this->shareStore()->findByTargetPath(TargetPath::fromString($targetPath)));
  }

  #[Test]
  #[DataProvider('shareWrites')]
  public function aWriteOnAnUnknownShareAnswersNotFound(string $write): void {
    $this->bootOwner();

    self::assertSame(404, $this->writeShare($write, self::UNKNOWN_SHARE_ID, $this->ownerToken));
  }

  #[Test]
  #[DataProvider('shareWrites')]
  public function aWriteOnARevokedShareAnswersNotFound(string $write): void {
    $shareId = $this->createAggregate();
    $this->revoke($shareId);

    self::assertSame(404, $this->writeShare($write, $shareId->toString(), $this->ownerToken));
  }

  #[Test]
  public function revokingAnUnknownShareAnswersNotFound(): void {
    self::assertCausedBy(NotFoundException::class, $this->failureOf(
      fn () => $this->revoke(ID::fromString(self::UNKNOWN_SHARE_ID)),
    ));
  }

  #[Test]
  public function revokingARevokedShareAnswersNotFound(): void {
    $shareId = $this->createAggregate();
    $this->revoke($shareId);

    self::assertCausedBy(NotFoundException::class, $this->failureOf(fn () => $this->revoke($shareId)));
  }

  #[Test]
  public function storingDispatchesDecoratedMessages(): void {
    $dispatcher = new RecordingMessageDispatcher();
    static::getContainer()->set(EventDispatcher::class, $dispatcher);
    $shareId = ID::generate();
    $shareable = ShareableReference::forImage(ID::generate());

    $this->shareStore()->store(Share::create(
      $shareId,
      $shareable,
      TargetPath::fromString('/image/decorated.png'),
      DateTime::now(),
      ShareContext::for($shareable),
    ));

    self::assertCount(1, $dispatcher->messages);
    $message = $dispatcher->messages[0];
    self::assertInstanceOf(ShareWasCreated::class, $message->payload());
    self::assertSame($shareId->toString(), $message->aggregateRootId()?->toString());
    self::assertSame('slink.share.domain.event.share_was_created', $message->header(Header::EVENT_TYPE));
    self::assertSame('slink.share.domain.share', $message->header(Header::AGGREGATE_ROOT_TYPE));
  }

  #[Test]
  public function theSecondOfTwoConcurrentWritersFailsAndTheFirstWritersValuesStay(): void {
    $shareId = $this->createAggregate();
    $firstContainer = $this->independentContainer();
    $secondContainer = $this->independentContainer();
    $firstLoad = $this->shareStoreOf($firstContainer)->get($shareId);
    $secondLoad = $this->shareStoreOf($secondContainer)->get($shareId);

    $this->expireAndStore($firstContainer, $firstLoad, '2041-01-01 00:00:00');

    self::assertCausedBy(OptimisticLockException::class, $this->failureOf(
      fn () => $this->expireAndStore($secondContainer, $secondLoad, '2042-02-02 00:00:00'),
    ));
    self::assertSame('2041-01-01 00:00:00', $this->reload($shareId)->getExpiresAt()?->format(self::TIMESTAMP_FORMAT));
  }

  private function bootOwner(): void {
    $this->createUser('share-store-owner@local.test', 'sharestoreowner', self::PASSWORD);
    $this->ownerToken = $this->login('sharestoreowner', self::PASSWORD);
  }

  private function configureShortening(bool $enabled, int $length): void {
    $this->saveSettings('share', ['enableUrlShortening' => $enabled, 'shortUrlLength' => $length]);
  }

  private function createUnpublishedShare(): string {
    $this->bootOwner();
    $this->configureShortening(true, self::SHORT_CODE_LENGTH);

    return $this->createImageShare($this->ownerToken, $this->uploadImage($this->ownerToken, false));
  }

  private function revoke(ID $shareId): void {
    $this->entityManager()->clear();
    $this->commandBus()->handleSync(new RevokeShareCommand($shareId->toString()));
  }

  private function expireAndStore(ContainerInterface $container, Share $share, string $expiresAt): void {
    $share->setExpiration(DateTime::fromString($expiresAt));
    $this->shareStoreOf($container)->store($share);

    /** @var EntityManagerInterface $entityManager */
    $entityManager = $container->get(EntityManagerInterface::class);
    $entityManager->flush();
  }

  private function shareStore(): ShareStoreRepositoryInterface {
    return $this->shareStoreOf(static::getContainer());
  }

  private function shareStoreOf(ContainerInterface $container): ShareStoreRepositoryInterface {
    /** @var ShareStoreRepositoryInterface $store */
    $store = $container->get(ShareStoreRepositoryInterface::class);

    return $store;
  }

  private function storeShortCode(string $shareId, string $shortCode): void {
    $this->readModel()->executeStatement(
      'UPDATE "short_url" SET short_code = ? WHERE share_id = ?',
      [$shortCode, $shareId],
    );
  }

  /**
   * @return array<string, mixed>|false
   */
  private function shortUrlOf(string $shareId): array|false {
    return $this->readModel()->fetchAssociative('SELECT uuid, short_code FROM "short_url" WHERE share_id = ?', [$shareId]);
  }

  private function shareVersion(string $shareId): mixed {
    return $this->readModel()->fetchOne('SELECT version FROM "share" WHERE uuid = ?', [$shareId]);
  }
}
