<?php

declare(strict_types=1);

namespace Unit\Slink\User\Application\Command\MoveOAuthProvider;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\Enum\SortDirection;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Application\Command\MoveOAuthProvider\MoveOAuthProviderCommand;
use Slink\User\Application\Command\MoveOAuthProvider\MoveOAuthProviderHandler;
use Slink\User\Domain\Exception\OAuthProviderNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use Slink\User\Domain\OAuthProvider;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Domain\Repository\OAuthProviderStoreRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

final class MoveOAuthProviderHandlerTest extends TestCase {

  private function expectSortOrderUpdate(MockObject $aggregate, float $sortOrder): void {
    $aggregate->expects($this->once())
      ->method('update')
      ->with(null, null, null, null, null, null, null, null, $sortOrder);
  }

  #[Test]
  public function itSwapsSortOrderOnMoveUp(): void {
    $targetId = ID::generate();
    $neighborId = ID::generate();

    $targetView = $this->createProviderWithSortOrder(2.0, $targetId);
    $neighborView = $this->createProviderWithSortOrder(1.0, $neighborId);

    $targetAggregate = $this->createMock(OAuthProvider::class);
    $this->expectSortOrderUpdate($targetAggregate, 1.0);

    $neighborAggregate = $this->createMock(OAuthProvider::class);
    $this->expectSortOrderUpdate($neighborAggregate, 2.0);

    $repository = $this->createMock(OAuthProviderRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('findById')
      ->with($this->callback(fn(ID $id) => $id->toString() === $targetId->toString()))
      ->willReturn($targetView);
    $repository->expects($this->once())
      ->method('findNeighbor')
      ->with(2.0, SortDirection::Up)
      ->willReturn($neighborView);

    $providerStore = $this->createMock(OAuthProviderStoreRepositoryInterface::class);
    $providerStore->expects($this->exactly(2))
      ->method('get')
      ->willReturnCallback(fn(ID $id) => match (true) {
        $id->toString() === $targetId->toString() => $targetAggregate,
        $id->toString() === $neighborId->toString() => $neighborAggregate,
        default => $this->fail('Unexpected ID: ' . $id->toString()),
      });
    $providerStore->expects($this->exactly(2))
      ->method('store');

    $handler = new MoveOAuthProviderHandler($providerStore, $repository);

    $command = new MoveOAuthProviderCommand($targetId->toString(), 'up');

    $handler($command);
  }

  #[Test]
  public function itSwapsSortOrderOnMoveDown(): void {
    $targetId = ID::generate();
    $neighborId = ID::generate();

    $targetView = $this->createProviderWithSortOrder(1.0, $targetId);
    $neighborView = $this->createProviderWithSortOrder(2.0, $neighborId);

    $targetAggregate = $this->createMock(OAuthProvider::class);
    $this->expectSortOrderUpdate($targetAggregate, 2.0);

    $neighborAggregate = $this->createMock(OAuthProvider::class);
    $this->expectSortOrderUpdate($neighborAggregate, 1.0);

    $repository = $this->createMock(OAuthProviderRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('findById')
      ->willReturn($targetView);
    $repository->expects($this->once())
      ->method('findNeighbor')
      ->with(1.0, SortDirection::Down)
      ->willReturn($neighborView);

    $providerStore = $this->createMock(OAuthProviderStoreRepositoryInterface::class);
    $providerStore->expects($this->exactly(2))
      ->method('get')
      ->willReturnCallback(fn(ID $id) => match (true) {
        $id->toString() === $targetId->toString() => $targetAggregate,
        $id->toString() === $neighborId->toString() => $neighborAggregate,
        default => $this->fail('Unexpected ID: ' . $id->toString()),
      });
    $providerStore->expects($this->exactly(2))
      ->method('store');

    $handler = new MoveOAuthProviderHandler($providerStore, $repository);

    $command = new MoveOAuthProviderCommand($targetId->toString(), 'down');

    $handler($command);
  }

  #[Test]
  public function itThrowsWhenNoTargetFound(): void {
    $targetId = ID::generate();

    $repository = $this->createMock(OAuthProviderRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('findById')
      ->willReturn(null);
    $repository->expects($this->never())
      ->method('findNeighbor');

    $providerStore = $this->createMock(OAuthProviderStoreRepositoryInterface::class);
    $providerStore->expects($this->never())->method('get');
    $providerStore->expects($this->never())->method('store');

    $handler = new MoveOAuthProviderHandler($providerStore, $repository);

    $command = new MoveOAuthProviderCommand($targetId->toString(), 'up');

    $this->expectException(OAuthProviderNotFoundException::class);

    $handler($command);
  }

  #[Test]
  public function itReturnsEarlyWhenNoNeighborFound(): void {
    $targetId = ID::generate();

    $targetView = $this->createStub(OAuthProviderView::class);
    $targetView->method('getSortOrder')->willReturn(1.0);

    $repository = $this->createMock(OAuthProviderRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('findById')
      ->willReturn($targetView);
    $repository->expects($this->once())
      ->method('findNeighbor')
      ->willReturn(null);

    $providerStore = $this->createMock(OAuthProviderStoreRepositoryInterface::class);
    $providerStore->expects($this->never())->method('get');
    $providerStore->expects($this->never())->method('store');

    $handler = new MoveOAuthProviderHandler($providerStore, $repository);

    $command = new MoveOAuthProviderCommand($targetId->toString(), 'up');

    $handler($command);
  }

  private function createProviderWithSortOrder(float $sortOrder, ?ID $id = null): OAuthProviderView {
    $id ??= ID::generate();
    $view = $this->createStub(OAuthProviderView::class);
    $view->method('getId')->willReturn($id->toString());
    $view->method('getSortOrder')->willReturn($sortOrder);

    return $view;
  }
}
