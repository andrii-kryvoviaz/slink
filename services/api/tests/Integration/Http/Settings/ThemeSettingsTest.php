<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Settings;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Slink\Settings\Domain\Enum\ColorMode;
use Slink\Settings\Domain\ValueObject\Customization\Theme;
use Tests\Integration\Http\HttpTestCase;

final class ThemeSettingsTest extends HttpTestCase {
  private const string THEME_CSS = ":root { --radius: 0; } /* \"quoted\" */\n.badge > span { content: \"ünïcode 🎨\"; }";

  private string $adminToken = '';

  protected function setUp(): void {
    parent::setUp();

    $userId = $this->createUser('themeadmin@local.test', 'themeadmin', self::PASSWORD);
    $this->grantAdmin($userId);
    $this->adminToken = $this->login('themeadmin', self::PASSWORD);
  }

  /**
   * @param array<string, mixed> $overrides
   * @return array<string, mixed>
   */
  private static function theme(string $id, array $overrides = []): array {
    return [
      'id' => $id,
      'name' => ucfirst($id),
      'light' => array_fill_keys(Theme::COLORS, '#ffffff'),
      'dark' => array_fill_keys(Theme::COLORS, '#000000'),
      'radius' => 0.5,
      'colorMode' => 'both',
      'css' => '',
      ...$overrides,
    ];
  }

  /**
   * @param array<string, mixed> $customization
   */
  private function save(array $customization, ?string $token = null): int {
    return $this->apiRequest(
      'POST',
      '/api/settings',
      $token ?? $this->adminToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['category' => 'customization', 'settings' => $customization], JSON_THROW_ON_ERROR),
    );
  }

  /**
   * @return array<string, mixed>
   */
  private function customization(string $path): array {
    self::assertSame(200, $this->apiRequest('GET', $path, $this->adminToken));

    $payload = $this->responsePayload();
    $customization = $payload['data']['customization'] ?? $payload['customization'] ?? null;
    self::assertIsArray($customization);

    return $customization;
  }

  private function selectTheme(string $theme): int {
    return $this->apiRequest(
      'PATCH',
      '/api/user/preferences',
      $this->adminToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['display.theme' => $theme], JSON_THROW_ON_ERROR),
    );
  }

  #[Test]
  public function itExposesDefaultsBeforeAnythingIsSaved(): void {
    $customization = $this->customization('/api/settings/public');

    self::assertArrayNotHasKey('customCss', $customization);
    self::assertTrue($customization['themeSelectorEnabled']);
    self::assertSame('default', $customization['defaultTheme']);
    self::assertSame([], $customization['hiddenThemes']);
    self::assertSame([], $customization['themes']);
    self::assertSame('both', $customization['colorMode']);
  }

  #[Test]
  public function itExposesBothColorModesBeforeAnythingIsSaved(): void {
    self::assertSame('both', $this->customization('/api/settings/global')['colorMode']);
  }

  /**
   * @return iterable<string, array{ColorMode}>
   */
  public static function colorModeProvider(): iterable {
    foreach (ColorMode::cases() as $mode) {
      yield $mode->value => [$mode];
    }
  }

  #[Test]
  #[DataProvider('colorModeProvider')]
  public function itPersistsEveryColorModeAndExposesItToAdminsAndPublic(ColorMode $mode): void {
    self::assertSame(204, $this->save(['colorMode' => $mode->value]));

    self::assertSame($mode->value, $this->customization('/api/settings/global')['colorMode']);
    self::assertSame($mode->value, $this->customization('/api/settings/public')['colorMode']);
  }

  #[Test]
  public function itPersistsThemesAndExposesThemToAdminsAndPublic(): void {
    $settings = [
      'siteName' => 'Acme',
      'siteDescription' => 'Screens',
      'logoUrl' => '',
      'themeSelectorEnabled' => false,
      'colorMode' => 'dark',
      'defaultTheme' => 'ocean',
      'hiddenThemes' => ['nord'],
      'themes' => [self::theme('ocean', ['css' => self::THEME_CSS]), self::theme('gruvbox')],
    ];

    self::assertSame(204, $this->save($settings));

    self::assertEquals($settings, $this->customization('/api/settings/global'));
    self::assertEquals($settings, $this->customization('/api/settings/public'));
    self::assertArrayNotHasKey('customCss', $this->customization('/api/settings/public'));
  }

  #[Test]
  public function itPersistsStockColorsForBuiltInThemeWithCss(): void {
    $stock = self::theme('nord', ['light' => null, 'dark' => null, 'css' => '.card { border-radius: 0; }']);

    self::assertSame(204, $this->save(['themes' => [$stock]]));

    $themes = $this->customization('/api/settings/public')['themes'];
    self::assertSame('nord', $themes[0]['id']);
    self::assertNull($themes[0]['light']);
    self::assertNull($themes[0]['dark']);
    self::assertSame('.card { border-radius: 0; }', $themes[0]['css']);
  }

  #[Test]
  public function itKeepsThemingDataWhenOnlySiteNameIsSaved(): void {
    $settings = [
      'siteName' => 'Acme',
      'siteDescription' => 'Screens',
      'logoUrl' => '',
      'themeSelectorEnabled' => false,
      'colorMode' => 'light',
      'defaultTheme' => 'ocean',
      'hiddenThemes' => ['nord'],
      'themes' => [self::theme('ocean', ['css' => self::THEME_CSS]), self::theme('gruvbox')],
    ];

    self::assertSame(204, $this->save($settings));
    self::assertSame(204, $this->save(['siteName' => 'Renamed']));

    $customization = $this->customization('/api/settings/global');
    self::assertSame('Renamed', $customization['siteName']);
    self::assertFalse($customization['themeSelectorEnabled']);
    self::assertSame('light', $customization['colorMode']);
    self::assertSame('ocean', $customization['defaultTheme']);
    self::assertSame(['nord'], $customization['hiddenThemes']);
    self::assertEquals($settings['themes'], $customization['themes']);
  }

  #[Test]
  public function itRemovesCssOfDeletedThemes(): void {
    self::assertSame(204, $this->save(['themes' => [self::theme('ocean', ['css' => 'body{}']), self::theme('forest', ['css' => 'h1{}'])]]));
    self::assertSame(204, $this->save(['themes' => [self::theme('forest', ['css' => 'h1{}'])]]));

    $themes = $this->customization('/api/settings/public')['themes'];
    self::assertSame(['forest'], array_column($themes, 'id'));
    self::assertSame(['h1{}'], array_column($themes, 'css'));
  }

  #[Test]
  public function itReplacesTheWholeListSoDeletedThemesDisappear(): void {
    self::assertSame(204, $this->save(['themes' => [self::theme('ocean'), self::theme('forest')], 'hiddenThemes' => ['nord']]));
    self::assertSame(204, $this->save(['themes' => [self::theme('forest')], 'hiddenThemes' => []]));

    $customization = $this->customization('/api/settings/global');
    self::assertSame(['forest'], array_column($customization['themes'], 'id'));
    self::assertSame([], $customization['hiddenThemes']);

    self::assertSame(204, $this->save(['themes' => []]));
    self::assertSame([], $this->customization('/api/settings/public')['themes']);
  }

  #[Test]
  public function itLetsUsersSelectOnlyAvailableThemes(): void {
    self::assertSame(422, $this->selectTheme('ocean'));

    self::assertSame(204, $this->save(['themes' => [self::theme('ocean')], 'hiddenThemes' => ['nord']]));

    self::assertContains($this->selectTheme('ocean'), [200, 204]);
    self::assertSame(422, $this->selectTheme('nord'));

    $this->apiRequest('GET', '/api/user/preferences', $this->adminToken);
    self::assertSame('ocean', $this->responsePayload()['data']['display.theme'] ?? null);
  }

  /**
   * @return iterable<string, array{array<string, mixed>, string}>
   */
  public static function invalidSettingsProvider(): iterable {
    yield 'oversized css' => [['themes' => [self::theme('ocean', ['css' => str_repeat('a', 65537)])]], 'customization.themes'];
    yield 'unknown color mode' => [['colorMode' => 'sepia'], 'customization.colorMode'];
    yield 'color mode not a string' => [['colorMode' => ['dark']], 'customization.colorMode'];
    yield 'theme with unknown color mode' => [['themes' => [self::theme('ocean', ['colorMode' => 'sepia'])]], 'customization.themes'];
    yield 'unknown default theme' => [['defaultTheme' => 'solarized'], 'customization.defaultTheme'];
    yield 'hidden default theme' => [['defaultTheme' => 'nord', 'hiddenThemes' => ['nord']], 'customization.defaultTheme'];
    yield 'hiding a custom theme' => [['themes' => [self::theme('ocean')], 'hiddenThemes' => ['ocean']], 'customization.hiddenThemes'];
    yield 'duplicate theme' => [['themes' => [self::theme('ocean'), self::theme('ocean')]], 'customization.themes'];
    yield 'malformed theme' => [['themes' => ['ocean']], 'customization.themes'];
    yield 'unsafe color' => [['themes' => [self::theme('ocean', ['light' => ['background' => 'url(x)'] + array_fill_keys(Theme::COLORS, '#ffffff')])]], 'customization.themes'];
    yield 'stock colors on custom theme' => [['themes' => [self::theme('ocean', ['light' => null, 'dark' => null])]], 'customization.themes'];
  }

  /**
   * @param array<string, mixed> $settings
   */
  #[Test]
  #[DataProvider('invalidSettingsProvider')]
  public function itRejectsInvalidSettingsWithAPropertyViolation(array $settings, string $property): void {
    self::assertSame(400, $this->save($settings));

    $violations = $this->responsePayload()['error']['violations'] ?? [];
    self::assertContains($property, array_column($violations, 'property'));
    self::assertSame([], $this->customization('/api/settings/public')['themes']);
  }

  #[Test]
  public function itForbidsNonAdmins(): void {
    $this->createUser('regular@local.test', 'regularuser', self::PASSWORD);

    self::assertSame(403, $this->save(['themes' => [self::theme('ocean', ['css' => 'body{}'])]], $this->login('regularuser', self::PASSWORD)));
  }
}
