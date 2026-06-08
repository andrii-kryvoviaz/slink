<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Domain\ValueObject\Image;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Exception\InvalidChunkSizeException;
use Slink\Settings\Domain\ValueObject\Image\ImageSettings;

final class ImageSettingsTest extends TestCase {
  #[Test]
  public function itDefaultsChunkSizeToHumanSizeString(): void {
    $settings = ImageSettings::fromPayload([
      'maxSize' => '5M',
    ]);

    $this->assertSame('2M', $settings->getChunkSize());
  }

  #[Test]
  public function itEmitsChunkSizeAsStringInPayload(): void {
    $settings = ImageSettings::fromPayload([
      'maxSize' => '5M',
      'chunkSize' => '4M',
    ]);

    $this->assertSame('4M', $settings->toPayload()['chunkSize']);
  }

  #[Test]
  public function itNormalizesLegacyIntChunkSizeToHumanSizeString(): void {
    $settings = ImageSettings::fromPayload([
      'maxSize' => '1000M',
      'chunkSize' => 2097152,
    ]);

    $this->assertSame('2M', $settings->getChunkSize());
  }

  #[Test]
  public function itKeepsHumanSizeStringChunkSizeAsIs(): void {
    $settings = ImageSettings::fromPayload([
      'maxSize' => '1000M',
      'chunkSize' => '2M',
    ]);

    $this->assertSame('2M', $settings->getChunkSize());
  }

  /**
   * @return iterable<string, array{string}>
   */
  public static function acceptedChunkSizeProvider(): iterable {
    yield 'lower bound 1M' => ['1M'];
    yield 'default 2M' => ['2M'];
    yield 'upper bound 25M' => ['25M'];
  }

  #[Test]
  #[DataProvider('acceptedChunkSizeProvider')]
  public function itAcceptsChunkSizeWithinRange(string $chunkSize): void {
    $settings = ImageSettings::fromPayload([
      'maxSize' => '5M',
      'chunkSize' => $chunkSize,
    ]);

    $this->assertSame($chunkSize, $settings->getChunkSize());
  }

  /**
   * @return iterable<string, array{string}>
   */
  public static function rejectedChunkSizeProvider(): iterable {
    yield 'zero 0M' => ['0M'];
    yield 'below minimum 512k' => ['512k'];
    yield 'above maximum 26M' => ['26M'];
    yield 'far above maximum 50M' => ['50M'];
    yield 'invalid unit 5G' => ['5G'];
    yield 'non-numeric abc' => ['abc'];
  }

  #[Test]
  #[DataProvider('rejectedChunkSizeProvider')]
  public function itRejectsChunkSizeOutsideRangeOrMalformed(string $chunkSize): void {
    $this->expectException(InvalidChunkSizeException::class);

    ImageSettings::fromPayload([
      'maxSize' => '5M',
      'chunkSize' => $chunkSize,
    ]);
  }

  #[Test]
  public function itThrowsExceptionExposingChunkSizeProperty(): void {
    $this->expectException(InvalidChunkSizeException::class);

    try {
      ImageSettings::fromPayload([
        'maxSize' => '5M',
        'chunkSize' => '26M',
      ]);
    } catch (InvalidChunkSizeException $exception) {
      $this->assertSame('image.chunkSize', $exception->getProperty());

      throw $exception;
    }
  }
}
