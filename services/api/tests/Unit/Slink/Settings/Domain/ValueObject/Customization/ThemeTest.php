<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Domain\ValueObject\Customization;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Exception\InvalidThemeException;
use Slink\Settings\Domain\ValueObject\Customization\Theme;
use Slink\User\Domain\Enum\DisplayTheme;

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
      'css' => '',
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
  public function itPutsCssLastInPayload(): void {
    $this->assertSame('css', array_key_last(Theme::fromPayload(self::payload())->toPayload()));
  }

  #[Test]
  public function itRoundTripsCssVerbatim(): void {
    $css = ":root { --radius: 0; }\n.badge::before { content: \"a > b\"; } /* ünïcode 🎨 */";

    $payload = Theme::fromPayload(self::payload(['css' => $css]))->toPayload();

    $this->assertSame($css, $payload['css']);
  }

  #[Test]
  public function itDefaultsMissingCssToEmptyString(): void {
    $payload = self::payload();
    unset($payload['css']);

    $this->assertSame('', Theme::fromPayload($payload)->toPayload()['css']);
  }

  #[Test]
  public function itTreatsNullCssAsEmptyString(): void {
    $this->assertSame('', Theme::fromPayload(self::payload(['css' => null]))->toPayload()['css']);
  }

  #[Test]
  public function itAcceptsCssAtMaxLength(): void {
    $css = str_repeat('a', 65536);

    $this->assertSame($css, Theme::fromPayload(self::payload(['css' => $css]))->toPayload()['css']);
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

  #[Test]
  #[DataProvider('builtInThemeProvider')]
  public function itAcceptsStockColorsForBuiltInThemes(string $id): void {
    $payload = Theme::fromPayload(self::payload(['id' => $id, 'light' => null, 'dark' => null]))->toPayload();

    $this->assertSame($id, $payload['id']);
    $this->assertNull($payload['light']);
    $this->assertNull($payload['dark']);
  }

  /**
   * @return iterable<string, array{string}>
   */
  public static function builtInThemeProvider(): iterable {
    foreach (DisplayTheme::values() as $id) {
      yield $id => [$id];
    }
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
    yield 'css too long' => [['css' => str_repeat('a', 65537)]];
    yield 'css not a string' => [['css' => ['a']]];
    yield 'css is an int' => [['css' => 5]];
    yield 'stock colors for custom theme' => [['light' => null, 'dark' => null]];
    yield 'only light null for built-in theme' => [['id' => 'nord', 'light' => null]];
    yield 'only dark null for built-in theme' => [['id' => 'nord', 'dark' => null]];
    yield 'only light null for custom theme' => [['light' => null]];
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
