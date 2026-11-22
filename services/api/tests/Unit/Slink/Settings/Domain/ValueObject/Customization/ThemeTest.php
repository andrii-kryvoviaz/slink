<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Domain\ValueObject\Customization;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Exception\InvalidThemeException;
use Slink\Settings\Domain\ValueObject\Customization\Theme;

final class ThemeTest extends TestCase {
  /**
   * @param array<string, mixed> $overrides
   * @return array<string, mixed>
   */
  private static function payload(array $overrides = []): array {
    return [
      'id' => 'ocean',
      'name' => 'Ocean',
      'light' => array_fill_keys(Theme::COLORS, '#ffffff'),
      'dark' => array_fill_keys(Theme::COLORS, '#000000'),
      'radius' => 0.5,
      ...$overrides,
    ];
  }

  #[Test]
  public function itRoundTripsPayload(): void {
    $theme = Theme::fromPayload(self::payload());

    $this->assertSame('ocean', $theme->getId());
    $this->assertSame(self::payload(), $theme->toPayload());
  }

  #[Test]
  public function itNormalizesNameAndColors(): void {
    $payload = Theme::fromPayload(self::payload([
      'name' => '  Ocean ',
      'light' => array_fill_keys(Theme::COLORS, '#ABCDEF'),
    ]))->toPayload();

    $this->assertSame('Ocean', $payload['name']);
    $this->assertSame(array_fill_keys(Theme::COLORS, '#abcdef'), $payload['light']);
  }

  #[Test]
  public function itDropsUnknownColors(): void {
    $payload = Theme::fromPayload(self::payload([
      'light' => [...array_fill_keys(Theme::COLORS, '#ffffff'), 'evil' => 'red;}'],
    ]))->toPayload();

    $this->assertSame(Theme::COLORS, array_keys($payload['light']));
  }

  #[Test]
  public function itAllowsMissingRadius(): void {
    $this->assertNull(Theme::fromPayload(self::payload(['radius' => null]))->toPayload()['radius']);
  }

  /**
   * @return iterable<string, array{array<string, mixed>}>
   */
  public static function invalidPayloadProvider(): iterable {
    yield 'missing id' => [['id' => null]];
    yield 'uppercase id' => [['id' => 'Ocean']];
    yield 'id with selector characters' => [['id' => "a'] *{}"]];
    yield 'id with trailing newline' => [["id" => "ocean\n"]];
    yield 'id too long' => [['id' => str_repeat('a', 49)]];
    yield 'non string id' => [['id' => ['ocean']]];
    yield 'blank name' => [['name' => '   ']];
    yield 'name too long' => [['name' => str_repeat('a', 49)]];
    yield 'missing palette' => [['light' => null]];
    yield 'incomplete palette' => [['dark' => ['background' => '#000000']]];
    yield 'named color' => [['light' => array_fill_keys(Theme::COLORS, 'red')]];
    yield 'css injection' => [['light' => array_fill_keys(Theme::COLORS, '#fff}</style>')]];
    yield 'color with trailing newline' => [['light' => array_fill_keys(Theme::COLORS, "#ffffff\n")]];
    yield 'negative radius' => [['radius' => -1]];
    yield 'huge radius' => [['radius' => 2.1]];
    yield 'string radius' => [['radius' => '1rem']];
  }

  /**
   * @param array<string, mixed> $overrides
   */
  #[Test]
  #[DataProvider('invalidPayloadProvider')]
  public function itRejectsInvalidPayload(array $overrides): void {
    $this->expectException(InvalidThemeException::class);

    Theme::fromPayload(self::payload($overrides));
  }
}
