<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Infrastructure\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Share\Domain\Repository\ShortUrlRepositoryInterface;
use Slink\Share\Infrastructure\Service\ShortCodeGenerator;

final class ShortCodeGeneratorTest extends TestCase {
  private ShortUrlRepositoryInterface $shortUrlRepository;
  private ShortCodeGenerator $generator;

  protected function setUp(): void {
    $this->shortUrlRepository = $this->createStub(ShortUrlRepositoryInterface::class);
    $this->generator = new ShortCodeGenerator($this->shortUrlRepository);
  }

  #[Test]
  public function itGeneratesCodeOfCorrectLength(): void {
    $this->shortUrlRepository
      ->method('existsByShortCode')
      ->willReturn(false);

    $code = $this->generator->generate();

    $this->assertEquals(8, strlen($code));
  }

  #[Test]
  public function itGeneratesCodeWithValidCharacters(): void {
    $this->shortUrlRepository
      ->method('existsByShortCode')
      ->willReturn(false);

    $code = $this->generator->generate();

    $this->assertMatchesRegularExpression('/^[0-9A-Za-z]{8}$/', $code);
  }

  #[Test]
  public function itRetriesOnCollision(): void {
    $shortUrlRepository = $this->createMock(ShortUrlRepositoryInterface::class);
    $shortUrlRepository
      ->expects($this->exactly(3))
      ->method('existsByShortCode')
      ->willReturnOnConsecutiveCalls(true, true, false);

    $generator = new ShortCodeGenerator($shortUrlRepository);
    $code = $generator->generate();

    $this->assertEquals(8, strlen($code));
  }

  #[Test]
  public function itThrowsExceptionAfterMaxAttempts(): void {
    $this->shortUrlRepository
      ->method('existsByShortCode')
      ->willReturn(true);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('Failed to generate unique short code after 10 attempts');

    $this->generator->generate();
  }

  #[Test]
  public function itGeneratesUniqueCodesOnMultipleCalls(): void {
    $this->shortUrlRepository
      ->method('existsByShortCode')
      ->willReturn(false);

    $codes = [];
    for ($i = 0; $i < 100; $i++) {
      $codes[] = $this->generator->generate();
    }

    $uniqueCodes = array_unique($codes);
    $this->assertCount(100, $uniqueCodes);
  }

  #[Test]
  public function itGeneratesUrlSafeCharactersOnly(): void {
    $this->shortUrlRepository
      ->method('existsByShortCode')
      ->willReturn(false);

    for ($i = 0; $i < 50; $i++) {
      $code = $this->generator->generate();
      $this->assertDoesNotMatchRegularExpression('/[^0-9A-Za-z]/', $code);
    }
  }
}
