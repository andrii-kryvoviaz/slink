<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Application\Command\SetSharePassword;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Share\Application\Command\SetSharePassword\SetSharePasswordCommand;
use Slink\Share\Application\Command\SetSharePassword\SetSharePasswordHandler;
use Slink\Share\Domain\Repository\ShareStoreRepositoryInterface;
use Slink\Share\Domain\Share;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class SetSharePasswordHandlerTest extends TestCase {
  private ShareStoreRepositoryInterface&MockObject $shareStore;
  private SetSharePasswordHandler $handler;

  protected function setUp(): void {
    parent::setUp();

    $this->shareStore = $this->createMock(ShareStoreRepositoryInterface::class);
    $access = $this->createStub(AuthorizationCheckerInterface::class);
    $access->method('isGranted')->willReturn(true);
    $this->handler = new SetSharePasswordHandler($this->shareStore, $access);
  }

  #[Test]
  public function itPassesPlaintextToAggregate(): void {
    $shareId = ID::generate()->toString();
    $plaintext = 'hunter2';
    $command = new SetSharePasswordCommand($plaintext);

    $share = $this->createMock(Share::class);
    $share->method('aggregateRootVersion')->willReturn(1);

    $share
      ->expects($this->once())
      ->method('setPassword')
      ->with($plaintext);

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
  public function itClearsPasswordWhenCommandValueIsNull(): void {
    $shareId = ID::generate()->toString();
    $command = new SetSharePasswordCommand(null);

    $share = $this->createMock(Share::class);
    $share->method('aggregateRootVersion')->willReturn(1);

    $share
      ->expects($this->once())
      ->method('setPassword')
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
    $command = new SetSharePasswordCommand('hunter2');

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
}
