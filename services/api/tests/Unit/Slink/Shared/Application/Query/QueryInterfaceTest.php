<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Application\Query;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Application\Query\QueryInterface;
use ReflectionClass;

final class QueryInterfaceTest extends TestCase {

  #[Test]
  public function itCanBeImplemented(): void {
    $query = new ConcreteQuery();

    $this->assertInstanceOf(QueryInterface::class, $query);
  }

  #[Test]
  public function itCanBeUsedAsTypeHint(): void {
    $query = new ConcreteQuery();

    $this->assertQueryInterface($query);
  }

  #[Test]
  public function itHasNoMethods(): void {
    $reflection = new ReflectionClass(QueryInterface::class);
    $methods = $reflection->getMethods();

    $this->assertCount(0, $methods);
  }

  #[Test]
  public function itIsAnInterface(): void {
    $reflection = new ReflectionClass(QueryInterface::class);

    $this->assertTrue($reflection->isInterface());
  }

  #[Test]
  public function itSupportsMultipleImplementations(): void {
    $query1 = new ConcreteQuery();
    $query2 = new AnotherConcreteQuery();

    $this->assertInstanceOf(QueryInterface::class, $query1);
    $this->assertInstanceOf(QueryInterface::class, $query2);
  }

  private function assertQueryInterface(QueryInterface $query): void {
    $this->assertInstanceOf(QueryInterface::class, $query);
  }
}

class ConcreteQuery implements QueryInterface {
  public function __construct(
    public readonly string $id = 'test-id'
  ) {
  }
}

class AnotherConcreteQuery implements QueryInterface {
  /**
   * @param array<string, mixed> $filters
   */
  public function __construct(
    public readonly array $filters = []
  ) {
  }
}
