<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Shared\Infrastructure\MessageBus\Middleware;

use Doctrine\DBAL\Driver\AbstractException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Infrastructure\MessageBus\Middleware\Retry;
use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Middleware\TraceableMiddleware;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Stopwatch\Stopwatch;

final class RetryTest extends TestCase {
  private int $calls = 0;

  /**
   * @return iterable<string, array{bool}>
   */
  public static function buses(): iterable {
    yield 'plain bus' => [false];
    yield 'traced bus' => [true];
  }

  private static function conflict(): UniqueConstraintViolationException {
    return new UniqueConstraintViolationException(new class('duplicate key') extends AbstractException {}, null);
  }

  /**
   * @param list<?\Throwable> $handlerFailures
   * @param list<?\Throwable> $flushFailures
   */
  private function bus(ManagerRegistry $registry, bool $traced, array $handlerFailures = [], array $flushFailures = []): MessageBus {
    $handler = function (\stdClass $message) use (&$handlerFailures): string {
      $this->calls++;
      $failure = \array_shift($handlerFailures);

      if ($failure !== null) {
        throw $failure;
      }

      return 'handled';
    };

    $flush = new class($flushFailures) implements MiddlewareInterface {
      /**
       * @param list<?\Throwable> $failures
       */
      public function __construct(private array $failures) {
      }

      public function handle(Envelope $envelope, StackInterface $stack): Envelope {
        $failure = \array_shift($this->failures);

        if ($failure !== null) {
          throw $failure;
        }

        return $stack->next()->handle($envelope, $stack);
      }
    };

    $middleware = [
      new Retry($registry),
      new HandleMessageMiddleware(new HandlersLocator([\stdClass::class => [$handler]])),
      $flush,
    ];

    if ($traced) {
      \array_unshift($middleware, new TraceableMiddleware(new Stopwatch(), 'messenger.bus.command'));
    }

    return new MessageBus(new RewindableGenerator(static fn (): \Generator => yield from $middleware, \count($middleware)));
  }

  private function registry(int $resets): ManagerRegistry {
    $registry = $this->createMock(ManagerRegistry::class);
    $registry->expects($this->exactly($resets))->method('resetManager');

    return $registry;
  }

  private function outcome(Envelope $envelope): mixed {
    return $envelope->last(HandledStamp::class)?->getResult();
  }

  #[Test]
  #[DataProvider('buses')]
  public function itRerunsTheCommandOnceWhenTheFlushHitsAUniqueConflict(bool $traced): void {
    $bus = $this->bus($this->registry(1), $traced, flushFailures: [self::conflict()]);

    $envelope = $bus->dispatch(new \stdClass());

    $this->assertSame(2, $this->calls);
    $this->assertSame('handled', $this->outcome($envelope));
  }

  #[Test]
  #[DataProvider('buses')]
  public function itRerunsTheCommandOnceWhenTheHandlerHitsAUniqueConflict(bool $traced): void {
    $bus = $this->bus($this->registry(1), $traced, handlerFailures: [self::conflict()]);

    $envelope = $bus->dispatch(new \stdClass());

    $this->assertSame(2, $this->calls);
    $this->assertSame('handled', $this->outcome($envelope));
  }

  #[Test]
  #[DataProvider('buses')]
  public function itRerunsTheCommandWhenAUniqueConflictIsNestedInAnotherHandlerFailure(bool $traced): void {
    $nested = new HandlerFailedException(new Envelope(new \stdClass()), ['inner' => self::conflict()]);
    $bus = $this->bus($this->registry(1), $traced, handlerFailures: [$nested]);

    $bus->dispatch(new \stdClass());

    $this->assertSame(2, $this->calls);
  }

  #[Test]
  #[DataProvider('buses')]
  public function itLetsASecondUniqueConflictFromTheFlushThroughUnchanged(bool $traced): void {
    $second = self::conflict();
    $bus = $this->bus($this->registry(1), $traced, flushFailures: [self::conflict(), $second]);

    try {
      $bus->dispatch(new \stdClass());
      $this->fail('The second conflict was swallowed.');
    } catch (UniqueConstraintViolationException $failure) {
      $this->assertSame($second, $failure);
    }

    $this->assertSame(2, $this->calls);
  }

  #[Test]
  #[DataProvider('buses')]
  public function itLetsASecondUniqueConflictFromTheHandlerThroughUnchanged(bool $traced): void {
    $second = self::conflict();
    $bus = $this->bus($this->registry(1), $traced, handlerFailures: [self::conflict(), $second]);

    try {
      $bus->dispatch(new \stdClass());
      $this->fail('The second conflict was swallowed.');
    } catch (HandlerFailedException $failure) {
      $this->assertSame([$second], \array_values($failure->getWrappedExceptions()));
    }

    $this->assertSame(2, $this->calls);
  }

  #[Test]
  #[DataProvider('buses')]
  public function itDoesNotRerunTheCommandWhenTheHandlerFailsForAnotherReason(bool $traced): void {
    $other = new \RuntimeException('Other');
    $bus = $this->bus($this->registry(0), $traced, handlerFailures: [$other]);

    try {
      $bus->dispatch(new \stdClass());
      $this->fail('The failure was swallowed.');
    } catch (HandlerFailedException $failure) {
      $this->assertSame([$other], \array_values($failure->getWrappedExceptions()));
    }

    $this->assertSame(1, $this->calls);
  }

  #[Test]
  #[DataProvider('buses')]
  public function itDoesNotRerunTheCommandWhenTheFlushFailsForAnotherReason(bool $traced): void {
    $other = new \RuntimeException('Other');
    $bus = $this->bus($this->registry(0), $traced, flushFailures: [$other]);

    $this->expectExceptionObject($other);

    try {
      $bus->dispatch(new \stdClass());
    } finally {
      $this->assertSame(1, $this->calls);
    }
  }
}
