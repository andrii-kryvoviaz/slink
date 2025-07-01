<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Application\Query;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Slink\Shared\Application\Query\QueryBusInterface;
use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Application\Query\QueryTrait;
use stdClass;
use Symfony\Component\Messenger\Envelope;

final class QueryTraitTest extends TestCase {

  #[Test]
  public function itAsksQueryWithEnvelope(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $query = $this->createMock(QueryInterface::class);
    $envelope = new Envelope($query);
    $expectedResult = 'envelope result';

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($envelope)
      ->willReturn($expectedResult);

    $traitUser = new ClassUsingQueryTrait();
    $traitUser->setQueryBus($queryBus);
    $result = $traitUser->testAsk($envelope);

    $this->assertEquals($expectedResult, $result);
  }

  #[Test]
  public function itCanHandleComplexQueries(): void {
    $traitUser = new ClassUsingQueryTrait();
    $queryBus = $this->createMock(QueryBusInterface::class);

    $complexQuery = new ComplexQuery(
      'search-term',
      ['status' => 'active'],
      10,
      0
    );

    $expectedResult = [
      'data' => ['item1', 'item2'],
      'total' => 2,
      'page' => 1
    ];

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($complexQuery)
      ->willReturn($expectedResult);

    $traitUser->setQueryBus($queryBus);
    $result = $traitUser->testAsk($complexQuery);

    $this->assertEquals($expectedResult, $result);
  }

  #[Test]
  public function itCanSetAndUseQueryBus(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $query = $this->createMock(QueryInterface::class);
    $expectedResult = 'query result';

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($query)
      ->willReturn($expectedResult);

    $traitUser = new ClassUsingQueryTrait();
    $traitUser->setQueryBus($queryBus);

    $result = $traitUser->testAsk($query);

    $this->assertEquals($expectedResult, $result);
    $this->assertSame($queryBus, $traitUser->getTestQueryBus());
  }

  #[Test]
  public function itHandlesDifferentReturnTypes(): void {
    $testCases = [
      'string result',
      123,
      ['array', 'result'],
      new stdClass(),
      null,
      true,
      false
    ];

    foreach ($testCases as $expectedResult) {
      $traitUser = new ClassUsingQueryTrait();
      $queryBus = $this->createMock(QueryBusInterface::class);
      $query = $this->createMock(QueryInterface::class);

      $queryBus->expects($this->once())
        ->method('ask')
        ->with($query)
        ->willReturn($expectedResult);

      $traitUser->setQueryBus($queryBus);
      $result = $traitUser->testAsk($query);

      $this->assertEquals($expectedResult, $result);
    }
  }

  #[Test]
  public function itHasPrivateQueryBusProperty(): void {
    $reflection = new ReflectionClass(QueryTrait::class);
    $property = $reflection->getProperty('queryBus');

    $this->assertTrue($property->isPrivate());
  }

  #[Test]
  public function itIsRequiredDependencyInjection(): void {
    $reflection = new ReflectionClass(QueryTrait::class);
    $method = $reflection->getMethod('setQueryBus');
    $attributes = $method->getAttributes();

    $hasRequiredAttribute = false;
    foreach ($attributes as $attribute) {
      if ($attribute->getName() === 'Symfony\Contracts\Service\Attribute\Required') {
        $hasRequiredAttribute = true;
        break;
      }
    }

    $this->assertTrue($hasRequiredAttribute);
  }
}

class ClassUsingQueryTrait {
  private ?QueryBusInterface $testQueryBus = null;

  public function getTestQueryBus(): ?QueryBusInterface {
    return $this->testQueryBus;
  }

  public function setQueryBus(QueryBusInterface $queryBus): void {
    $this->testQueryBus = $queryBus;
  }

  public function testAsk(QueryInterface|Envelope $query): mixed {
    return $this->testQueryBus?->ask($query);
  }
}

readonly class ComplexQuery implements QueryInterface {
  /**
   * @param array<string, mixed> $filters
   */
  public function __construct(
    public string $searchTerm,
    public array  $filters,
    public int    $limit,
    public int    $offset
  ) {
  }
}
