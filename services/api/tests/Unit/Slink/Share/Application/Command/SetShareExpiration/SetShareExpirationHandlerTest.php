<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Application\Command\SetShareExpiration;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Share\Application\Command\SetShareExpiration\SetShareExpirationCommand;
use Slink\Share\Application\Command\SetShareExpiration\SetShareExpirationHandler;
use Slink\Share\Domain\Exception\ShareAccessDeniedException;
use Slink\Share\Domain\Repository\ShareStoreRepositoryInterface;
use Slink\Share\Domain\Share;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class SetShareExpirationHandlerTest extends TestCase {
  private ShareStoreRepositoryInterface&MockObject $shareStore;
  private SetShareExpirationHandler $handler;

  protected function setUp(): void {
    parent::setUp();

    $this->shareStore = $this->createMock(ShareStoreRepositoryInterface::class);
    $this->handler = $this->createHandler(accessGranted: true);
  }

  private function createHandler(bool $accessGranted): SetShareExpirationHandler {
    $access = $this->createStub(AuthorizationCheckerInterface::class);
    $access->method('isGranted')->willReturn($accessGranted);

    return new SetShareExpirationHandler($this->shareStore, $access);
  }

  #[Test]
  public function itSetsExpirationFromDateString(): void {
    $shareId = ID::generate()->toString();
    $command = new SetShareExpirationCommand(new \DateTimeImmutable('2099-12-31T23:59:59.000Z'));

    $share = $this->createMock(Share::class);
    $share->method('aggregateRootVersion')->willReturn(1);

    $share
      ->expects($this->once())
      ->method('setExpiration')
      ->with($this->callback(function ($value): bool {
        return $value instanceof DateTime
          && $value->toString() === '2099-12-31T23:59:59.000000+00:00';
      }));

    $this->shareStore
      ->expects($this->once())
      ->method('get')
      ->with($this->callback(fn(ID $id): bool => $id->toString() === $shareId))
      ->willReturn($share);

    $this->shareStore
      ->expects($this->once())
      ->method('store')
      ->with($share);

    $this->handler->__invoke($command, $shareId);
  }

  #[Test]
  public function itClearsExpirationWhenCommandValueIsNull(): void {
    $shareId = ID::generate()->toString();
    $command = new SetShareExpirationCommand(null);

    $share = $this->createMock(Share::class);
    $share->method('aggregateRootVersion')->willReturn(1);

    $share
      ->expects($this->once())
      ->method('setExpiration')
      ->with(null);

    $this->shareStore
      ->method('get')
      ->willReturn($share);

    $this->shareStore
      ->expects($this->once())
      ->method('store')
      ->with($share);

    $this->handler->__invoke($command, $shareId);
  }

  #[Test]
  public function itThrowsNotFoundWhenAggregateMissing(): void {
    $shareId = ID::generate()->toString();
    $command = new SetShareExpirationCommand(null);

    $share = $this->createStub(Share::class);
    $share->method('aggregateRootVersion')->willReturn(0);

    $this->shareStore
      ->method('get')
      ->willReturn($share);

    $this->shareStore
      ->expects($this->never())
      ->method('store');

    $this->expectException(NotFoundException::class);

    $this->handler->__invoke($command, $shareId);
  }

  #[Test]
  public function itDeniesAccessWhenVoterRejectsCaller(): void {
    $handler = $this->createHandler(accessGranted: false);
    $shareId = ID::generate()->toString();
    $command = new SetShareExpirationCommand(null);

    $share = $this->createMock(Share::class);
    $share->method('aggregateRootVersion')->willReturn(1);
    $share->expects($this->never())->method('setExpiration');

    $this->shareStore
      ->method('get')
      ->willReturn($share);

    $this->shareStore
      ->expects($this->never())
      ->method('store');

    $this->expectException(ShareAccessDeniedException::class);

    $handler->__invoke($command, $shareId);
  }
}
