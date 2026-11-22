<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Domain\ValueObject\Customization;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\Event\SettingsChanged;
use Slink\Settings\Domain\Exception\InvalidDefaultThemeException;
use Slink\Settings\Domain\Exception\InvalidHiddenThemesException;
use Slink\Settings\Domain\Exception\InvalidLogoUrlException;
use Slink\Settings\Domain\Exception\InvalidSiteDescriptionException;
use Slink\Settings\Domain\Exception\InvalidSiteNameException;
use Slink\Settings\Domain\Exception\InvalidThemeException;
use Slink\Settings\Domain\ValueObject\Customization\CustomizationSettings;
use Slink\Settings\Domain\ValueObject\Customization\Theme;
use Slink\Shared\Domain\Exception\SpecificationException;

final class CustomizationSettingsTest extends TestCase {
  #[Test]
  public function itRoundTripsPayload(): void {
    $payload = [
      'siteName' => 'My Site',
      'siteDescription' => 'My description',
      'logoUrl' => 'https://example.com/logo.png',
      'themeSelectorEnabled' => false,
      'defaultTheme' => 'ocean',
      'hiddenThemes' => ['nord'],
      'themes' => [self::theme('ocean', ['css' => 'body { color: red; }'])],
    ];

    $settings = CustomizationSettings::fromPayload($payload);

    $this->assertSame('My Site', $settings->getSiteName());
    $this->assertSame('My description', $settings->getSiteDescription());
    $this->assertSame('https://example.com/logo.png', $settings->getLogoUrl());
    $this->assertFalse($settings->isThemeSelectorEnabled());
    $this->assertSame('ocean', $settings->getDefaultTheme());
    $this->assertSame($payload, $settings->toPayload());
  }

  #[Test]
  public function itKeepsListsWholeAcrossEventSerialization(): void {
    $settings = CustomizationSettings::fromPayload([
      'hiddenThemes' => ['nord'],
      'themes' => [self::theme('ocean'), self::theme('forest')],
    ]);

    $this->assertSame(['nord'], $settings->toNormalizedPayload()['customization.hiddenThemes']);
    $this->assertCount(2, $settings->toNormalizedPayload()['customization.themes']);

    $event = new SettingsChanged(SettingCategory::Customization, $settings);
    $restored = SettingsChanged::fromPayload(json_decode((string) json_encode($event->toPayload()), true));

    $this->assertEquals($settings->toPayload(), $restored->settings->toPayload());
  }

  #[Test]
  public function itRestoresEventsStoredBeforeThemesExisted(): void {
    $event = SettingsChanged::fromPayload([
      'category' => 'customization',
      'settings' => [
        'customization.siteName' => 'Acme',
        'customization.siteDescription' => 'Screens',
        'customization.logoUrl' => '',
        'customization.customCss' => 'body { color: red; }',
      ],
    ]);

    $payload = $event->settings->toPayload();

    $this->assertSame('Acme', $payload['siteName']);
    $this->assertTrue($payload['themeSelectorEnabled']);
    $this->assertSame('default', $payload['defaultTheme']);
    $this->assertSame([], $payload['themes']);
    $this->assertArrayNotHasKey('customCss', $payload);
  }

  #[Test]
  public function itIgnoresLegacyGlobalCustomCss(): void {
    $payload = CustomizationSettings::fromPayload(['customCss' => 'body { color: red; }'])->toPayload();

    $this->assertArrayNotHasKey('customCss', $payload);
  }

  #[Test]
  public function itListsBuiltInAndCustomThemesExceptHiddenOnes(): void {
    $available = CustomizationSettings::fromPayload([
      'hiddenThemes' => ['nord'],
      'themes' => [self::theme('gruvbox'), self::theme('nord'), self::theme('ocean')],
    ])->getAvailableThemes();

    $this->assertContains('default', $available);
    $this->assertContains('ocean', $available);
    $this->assertNotContains('nord', $available);
    $this->assertCount(1, array_keys($available, 'gruvbox', true));
  }

  /**
   * @return iterable<string, array{array<string, mixed>, class-string<SpecificationException>, string}>
   */
  public static function invalidThemingProvider(): iterable {
    yield 'theme css too long' => [['themes' => [self::theme('ocean', ['css' => str_repeat('a', 65537)])]], InvalidThemeException::class, 'customization.themes'];
    yield 'theme css not a string' => [['themes' => [self::theme('ocean', ['css' => ['a']])]], InvalidThemeException::class, 'customization.themes'];
    yield 'unknown default theme' => [['defaultTheme' => 'solarized'], InvalidDefaultThemeException::class, 'customization.defaultTheme'];
    yield 'hidden default theme' => [['hiddenThemes' => ['default']], InvalidDefaultThemeException::class, 'customization.defaultTheme'];
    yield 'default theme not a string' => [['defaultTheme' => []], InvalidDefaultThemeException::class, 'customization.defaultTheme'];
    yield 'unknown hidden theme' => [['hiddenThemes' => ['solarized']], InvalidHiddenThemesException::class, 'customization.hiddenThemes'];
    yield 'hidden themes not a list' => [['hiddenThemes' => 'nord'], InvalidHiddenThemesException::class, 'customization.hiddenThemes'];
    yield 'hidden themes of wrong type' => [['hiddenThemes' => [1]], InvalidHiddenThemesException::class, 'customization.hiddenThemes'];
    yield 'themes not a list' => [['themes' => 'ocean'], InvalidThemeException::class, 'customization.themes'];
    yield 'theme not an object' => [['themes' => ['ocean']], InvalidThemeException::class, 'customization.themes'];
    yield 'duplicate theme ids' => [['themes' => [self::theme('a'), self::theme('a')]], InvalidThemeException::class, 'customization.themes'];
    yield 'too many themes' => [['themes' => array_map(static fn (int $i): array => self::theme('t' . $i), range(1, 51))], InvalidThemeException::class, 'customization.themes'];
  }

  /**
   * @param array<string, mixed> $payload
   * @param class-string<SpecificationException> $exception
   */
  #[Test]
  #[DataProvider('invalidThemingProvider')]
  public function itRejectsInvalidTheming(array $payload, string $exception, string $property): void {
    try {
      CustomizationSettings::fromPayload($payload);
    } catch (SpecificationException $e) {
      $this->assertInstanceOf($exception, $e);
      $this->assertSame($property, $e->getProperty());

      return;
    }

    $this->fail('Expected ' . $exception);
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
      'radius' => null,
      'css' => '',
      ...$overrides,
    ];
  }

  #[Test]
  public function itAppliesDefaultsOnEmptyPayload(): void {
    $settings = CustomizationSettings::fromPayload([]);

    $this->assertSame(CustomizationSettings::DEFAULT_SITE_NAME, $settings->getSiteName());
    $this->assertSame(CustomizationSettings::DEFAULT_SITE_DESCRIPTION, $settings->getSiteDescription());
    $this->assertSame('', $settings->getLogoUrl());
    $this->assertTrue($settings->isThemeSelectorEnabled());
    $this->assertSame(CustomizationSettings::DEFAULT_THEME, $settings->getDefaultTheme());
  }

  #[Test]
  public function itAcceptsSiteNameAtMaxLength(): void {
    $settings = CustomizationSettings::fromPayload([
      'siteName' => str_repeat('a', 64),
    ]);

    $this->assertSame(str_repeat('a', 64), $settings->getSiteName());
  }

  #[Test]
  public function itRejectsSiteNameLongerThanMaxLength(): void {
    $this->expectException(InvalidSiteNameException::class);

    CustomizationSettings::fromPayload([
      'siteName' => str_repeat('a', 65),
    ]);
  }

  #[Test]
  public function itAcceptsSiteDescriptionAtMaxLength(): void {
    $settings = CustomizationSettings::fromPayload([
      'siteDescription' => str_repeat('a', 255),
    ]);

    $this->assertSame(str_repeat('a', 255), $settings->getSiteDescription());
  }

  #[Test]
  public function itRejectsSiteDescriptionLongerThanMaxLength(): void {
    $this->expectException(InvalidSiteDescriptionException::class);

    CustomizationSettings::fromPayload([
      'siteDescription' => str_repeat('a', 256),
    ]);
  }

  /**
   * @return iterable<string, array{string}>
   */
  public static function acceptedLogoUrlProvider(): iterable {
    yield 'absolute url' => ['https://example.com/logo.png'];
    yield 'relative path' => ['/relative/path.png'];
    yield 'empty string' => [''];
  }

  #[Test]
  #[DataProvider('acceptedLogoUrlProvider')]
  public function itAcceptsValidLogoUrl(string $logoUrl): void {
    $settings = CustomizationSettings::fromPayload([
      'logoUrl' => $logoUrl,
    ]);

    $this->assertSame($logoUrl, $settings->getLogoUrl());
  }

  #[Test]
  public function itRejectsInvalidLogoUrl(): void {
    $this->expectException(InvalidLogoUrlException::class);

    CustomizationSettings::fromPayload([
      'logoUrl' => 'not a url',
    ]);
  }

  #[Test]
  public function itThrowsExceptionExposingLogoUrlProperty(): void {
    $this->expectException(InvalidLogoUrlException::class);

    try {
      CustomizationSettings::fromPayload([
        'logoUrl' => 'not a url',
      ]);
    } catch (InvalidLogoUrlException $exception) {
      $this->assertSame('customization.logoUrl', $exception->getProperty());

      throw $exception;
    }
  }
}
