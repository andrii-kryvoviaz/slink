<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Store;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Tests\Integration\Http\HttpTestCase;

/**
 * @template TAggregate of object
 */
abstract class ViewBackedStoreRoundTripTestCase extends HttpTestCase {
  use BootsIndependentKernels;

  abstract protected function createAggregate(): ID;

  /**
   * @return TAggregate
   */
  abstract protected function fetch(ID $id): object;

  /**
   * @param TAggregate $aggregate
   * @return array<string, mixed>
   */
  abstract protected function aggregateState(object $aggregate): array;

  /**
   * @return array<string, mixed>
   */
  abstract protected function storedState(ID $id): array;

  abstract protected function rootTable(): string;

  protected function rootIdColumn(): string {
    return 'uuid';
  }

  /**
   * @param array<string, string> $server
   */
  #[\Override]
  protected function apiRequest(
    string $method,
    string $path,
    ?string $token = null,
    array $server = [],
    ?string $body = null,
  ): int {
    $this->entityManager()->clear();

    return parent::apiRequest($method, $path, $token, $server, $body);
  }

  protected function tearDown(): void {
    $this->shutDownIndependentKernels();

    parent::tearDown();
  }

  #[Test]
  public function reloadedAggregateHoldsTheStoredState(): void {
    $id = $this->createAggregate();

    self::assertSame($this->storedState($id), $this->aggregateState($this->reload($id)));
  }

  #[Test]
  public function reloadingAfterTheRootRowIsDeletedThrowsNotFound(): void {
    $id = $this->createAggregate();
    $this->readModel()->executeStatement(
      \sprintf('DELETE FROM "%s" WHERE %s = ?', $this->rootTable(), $this->rootIdColumn()),
      [$id->toString()],
    );

    $this->expectException(NotFoundException::class);

    $this->reload($id);
  }

  /**
   * @return TAggregate
   */
  protected function reload(ID $id): object {
    $this->entityManager()->clear();

    return $this->fetch($id);
  }

  protected function entityManager(): EntityManagerInterface {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return $entityManager;
  }

  protected function readModel(): Connection {
    return $this->entityManager()->getConnection();
  }

  /**
   * @param callable(): void $action
   */
  protected function failureOf(callable $action): ?\Throwable {
    try {
      $action();
    } catch (\Throwable $failure) {
      return $failure;
    }

    return null;
  }

  /**
   * @param class-string<\Throwable> $expected
   */
  protected static function assertCausedBy(string $expected, ?\Throwable $failure): void {
    $causes = [];

    for ($cause = $failure; $cause !== null; $cause = $cause->getPrevious()) {
      if ($cause instanceof $expected) {
        self::assertInstanceOf($expected, $cause);

        return;
      }

      $causes[] = $cause::class . ': ' . $cause->getMessage();
    }

    self::fail(\sprintf('Expected %s, got: %s', $expected, \implode(' <- ', $causes) ?: 'no failure'));
  }

  protected function secondsOf(mixed $timestamp): ?string {
    if (!\is_string($timestamp)) {
      return null;
    }

    return \substr($timestamp, 0, 19);
  }
}
