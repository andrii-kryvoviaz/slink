<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Infrastructure\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Collection\Domain\ValueObject\CollectionName;
use Slink\Collection\Infrastructure\Service\UniqueCollectionNameGenerator;
use Slink\Shared\Domain\ValueObject\ID;

final class UniqueCollectionNameGeneratorTest extends TestCase {
  private CollectionRepositoryInterface&MockObject $repository;
  private UniqueCollectionNameGenerator $generator;
  private ID $userId;

  protected function setUp(): void {
    $this->repository = $this->createMock(CollectionRepositoryInterface::class);
    $this->generator = new UniqueCollectionNameGenerator($this->repository);
    $this->userId = ID::generate();
  }

  #[Test]
  public function itReturnsOriginalNameWhenNoCollectionsExist(): void {
    $this->repository
      ->method('findNamesByPatternAndUser')
      ->willReturn([]);

    $result = $this->generator->generate(
      CollectionName::fromString('My Collection'),
      $this->userId
    );

    $this->assertEquals('My Collection', $result->toString());
  }

  #[Test]
  public function itReturnsOriginalNameWhenOnlyNumberedVariantsExist(): void {
    $this->repository
      ->method('findNamesByPatternAndUser')
      ->willReturn(['My Collection (1)', 'My Collection (2)']);

    $result = $this->generator->generate(
      CollectionName::fromString('My Collection'),
      $this->userId
    );

    $this->assertEquals('My Collection', $result->toString());
  }

  #[Test]
  public function itAppendsSuffixWhenExactNameExists(): void {
    $this->repository
      ->method('findNamesByPatternAndUser')
      ->willReturn(['My Collection']);

    $result = $this->generator->generate(
      CollectionName::fromString('My Collection'),
      $this->userId
    );

    $this->assertEquals('My Collection (1)', $result->toString());
  }

  #[Test]
  public function itFindNextAvailableSuffix(): void {
    $this->repository
      ->method('findNamesByPatternAndUser')
      ->willReturn(['My Collection', 'My Collection (1)', 'My Collection (2)']);

    $result = $this->generator->generate(
      CollectionName::fromString('My Collection'),
      $this->userId
    );

    $this->assertEquals('My Collection (3)', $result->toString());
  }

  #[Test]
  public function itHandlesGapsInSuffixNumbers(): void {
    $this->repository
      ->method('findNamesByPatternAndUser')
      ->willReturn(['My Collection', 'My Collection (1)', 'My Collection (5)']);

    $result = $this->generator->generate(
      CollectionName::fromString('My Collection'),
      $this->userId
    );

    $this->assertEquals('My Collection (6)', $result->toString());
  }

  #[Test]
  public function itHandlesUnorderedResults(): void {
    $this->repository
      ->method('findNamesByPatternAndUser')
      ->willReturn(['My Collection (3)', 'My Collection', 'My Collection (1)']);

    $result = $this->generator->generate(
      CollectionName::fromString('My Collection'),
      $this->userId
    );

    $this->assertEquals('My Collection (4)', $result->toString());
  }

  #[Test]
  public function itIgnoresNonMatchingPatterns(): void {
    $this->repository
      ->method('findNamesByPatternAndUser')
      ->willReturn(['My Collection', 'My Collection (abc)', 'My Collection (1) extra']);

    $result = $this->generator->generate(
      CollectionName::fromString('My Collection'),
      $this->userId
    );

    $this->assertEquals('My Collection (1)', $result->toString());
  }

  #[Test]
  public function itHandlesNameWithParenthesesLiterally(): void {
    $this->repository
      ->method('findNamesByPatternAndUser')
      ->willReturn(['Test (1)']);

    $result = $this->generator->generate(
      CollectionName::fromString('Test (1)'),
      $this->userId
    );

    $this->assertEquals('Test (1) (1)', $result->toString());
  }

  #[Test]
  public function itPassesCorrectParametersToRepository(): void {
    $baseName = 'My Collection';

    $this->repository
      ->expects($this->once())
      ->method('findNamesByPatternAndUser')
      ->with($baseName, $this->userId->toString())
      ->willReturn([]);

    $this->generator->generate(
      CollectionName::fromString($baseName),
      $this->userId
    );
  }
}
