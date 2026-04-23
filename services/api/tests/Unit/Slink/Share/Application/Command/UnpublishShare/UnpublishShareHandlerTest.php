<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Application\Command\UnpublishShare;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Share\Application\Command\UnpublishShare\UnpublishShareCommand;
use Slink\Share\Application\Command\UnpublishShare\UnpublishShareHandler;
use Slink\Share\Domain\Exception\ShareAccessDeniedException;
use Slink\Share\Domain\Repository\ShareStoreRepositoryInterface;
use Slink\Share\Domain\Share;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class UnpublishShareHandlerTest extends TestCase {
  private ShareStoreRepositoryInterface&MockObject $shareStore;
  private UnpublishShareHandler $handler;

  protected function setUp(): void {
    parent::setUp();

    $this->shareStore = $this->createMock(ShareStoreRepositoryInterface::class);
    $this->handler = $this->createHandler(accessGranted: true);
  }

  private function createHandler(bool $accessGranted): UnpublishShareHandler {
    $access = $this->createStub(AuthorizationCheckerInterface::class);
    $access->method('isGranted')->willReturn($accessGranted);

    return new UnpublishShareHandler($this->shareStore, $access);
  }

  #[Test]
  public function itUnpublishesShare(): void {
    $shareId = ID::generate()->toString();
    $command = new UnpublishShareCommand($shareId);

    $share = $this->createMock(Share::class);
    $share->method('aggregateRootVersion')->willReturn(1);

    $share
      ->expects($this->once())
      ->method('unpublish');

    $this->shareStore
      ->expects($this->once())
      ->method('get')
      ->with($this->callback(fn(ID $id): bool => $id->toString() === $shareId))
      ->willReturn($share);

    $this->shareStore
      ->expects($this->once())
      ->method('store')
      ->with($share);

    $this->handler->__invoke($command);
  }

  #[Test]
  public function itThrowsNotFoundWhenAggregateMissing(): void {
    $shareId = ID::generate()->toString();
    $command = new UnpublishShareCommand($shareId);

    $share = $this->createStub(Share::class);
    $share->method('aggregateRootVersion')->willReturn(0);

    $this->shareStore
      ->method('get')
      ->willReturn($share);

    $this->shareStore
      ->expects($this->never())
      ->method('store');

    $this->expectException(NotFoundException::class);

    $this->handler->__invoke($command);
  }

  #[Test]
  public function itDeniesAccessWhenVoterRejectsCaller(): void {
    $handler = $this->createHandler(accessGranted: false);
    $shareId = ID::generate()->toString();
    $command = new UnpublishShareCommand($shareId);

    $share = $this->createMock(Share::class);
    $share->method('aggregateRootVersion')->willReturn(1);
    $share->expects($this->never())->method('unpublish');

    $this->shareStore
      ->method('get')
      ->willReturn($share);

    $this->shareStore
      ->expects($this->never())
      ->method('store');

    $this->expectException(ShareAccessDeniedException::class);

    $handler->__invoke($command);
  }
}
