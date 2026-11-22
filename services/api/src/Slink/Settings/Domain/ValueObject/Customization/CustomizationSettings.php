<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Customization;

use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\Exception\InvalidCustomCssException;
use Slink\Settings\Domain\Exception\InvalidDefaultThemeException;
use Slink\Settings\Domain\Exception\InvalidHiddenThemesException;
use Slink\Settings\Domain\Exception\InvalidLogoUrlException;
use Slink\Settings\Domain\Exception\InvalidSiteDescriptionException;
use Slink\Settings\Domain\Exception\InvalidSiteNameException;
use Slink\Settings\Domain\Exception\InvalidThemeException;
use Slink\Settings\Domain\ValueObject\AbstractSettingsValueObject;
use Slink\User\Domain\Enum\DisplayTheme;

final readonly class CustomizationSettings extends AbstractSettingsValueObject {
  public const string DEFAULT_SITE_NAME = 'Slink';
  public const string DEFAULT_SITE_DESCRIPTION = 'Fast and secure image sharing service';
  public const string DEFAULT_THEME = 'default';

  private const int MAX_SITE_NAME_LENGTH = 64;
  private const int MAX_SITE_DESCRIPTION_LENGTH = 255;
  private const int MAX_CUSTOM_CSS_LENGTH = 65536;
  private const int MAX_THEMES = 50;

  /**
   * @param list<string> $hiddenThemes
   * @param list<Theme> $themes
   */
  private function __construct(
    private string $siteName,
    private string $siteDescription,
    private string $logoUrl,
    private string $customCss,
    private bool $themeSelectorEnabled,
    private string $defaultTheme,
    private array $hiddenThemes,
    private array $themes,
  ) {
    if (mb_strlen($siteName) > self::MAX_SITE_NAME_LENGTH) {
      throw new InvalidSiteNameException('Site name cannot be longer than 64 characters');
    }

    if (mb_strlen($siteDescription) > self::MAX_SITE_DESCRIPTION_LENGTH) {
      throw new InvalidSiteDescriptionException('Site description cannot be longer than 255 characters');
    }

    if ($logoUrl !== '' && !filter_var($logoUrl, FILTER_VALIDATE_URL) && !str_starts_with($logoUrl, '/')) {
      throw new InvalidLogoUrlException();
    }

    if (mb_strlen($customCss) > self::MAX_CUSTOM_CSS_LENGTH) {
      throw new InvalidCustomCssException('Custom CSS cannot be longer than 65536 characters');
    }

    if (count($themes) > self::MAX_THEMES) {
      throw new InvalidThemeException('Cannot define more than 50 themes');
    }

    if (count(array_unique($this->getThemeIds())) !== count($themes)) {
      throw new InvalidThemeException('Theme ids must be unique');
    }

    if (array_diff($hiddenThemes, DisplayTheme::values()) !== []) {
      throw new InvalidHiddenThemesException('Only built-in themes can be hidden');
    }

    if (!in_array($defaultTheme, $this->getAvailableThemes(), true)) {
      throw new InvalidDefaultThemeException('Default theme must be one of the available themes');
    }
  }

  #[\Override]
  public function toPayload(): array {
    return [
      'siteName' => $this->siteName,
      'siteDescription' => $this->siteDescription,
      'logoUrl' => $this->logoUrl,
      'customCss' => $this->customCss,
      'themeSelectorEnabled' => $this->themeSelectorEnabled,
      'defaultTheme' => $this->defaultTheme,
      'hiddenThemes' => $this->hiddenThemes,
      'themes' => array_map(static fn (Theme $theme): array => $theme->toPayload(), $this->themes),
    ];
  }

  /**
   * Lists stay whole: flattened entries of a removed theme would outlive the save.
   */
  #[\Override]
  public function toNormalizedPayload(): array {
    $normalized = [];

    foreach ($this->toPayload() as $key => $value) {
      $normalized[$this . '.' . $key] = $value;
    }

    return $normalized;
  }

  #[\Override]
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['siteName'] ?? self::DEFAULT_SITE_NAME,
      $payload['siteDescription'] ?? self::DEFAULT_SITE_DESCRIPTION,
      $payload['logoUrl'] ?? '',
      self::parseCustomCss($payload['customCss'] ?? ''),
      filter_var($payload['themeSelectorEnabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
      self::parseDefaultTheme($payload['defaultTheme'] ?? ''),
      self::parseHiddenThemes($payload['hiddenThemes'] ?? []),
      self::parseThemes($payload['themes'] ?? []),
    );
  }

  #[\Override]
  public function getSettingsCategory(): SettingCategory {
    return SettingCategory::Customization;
  }

  public function getSiteName(): string {
    return $this->siteName;
  }

  public function getSiteDescription(): string {
    return $this->siteDescription;
  }

  public function getLogoUrl(): string {
    return $this->logoUrl;
  }

  public function getCustomCss(): string {
    return $this->customCss;
  }

  public function isThemeSelectorEnabled(): bool {
    return $this->themeSelectorEnabled;
  }

  public function getDefaultTheme(): string {
    return $this->defaultTheme;
  }

  /**
   * @return list<string>
   */
  public function getAvailableThemes(): array {
    return self::resolveAvailableThemes($this->hiddenThemes, $this->getThemeIds());
  }

  /**
   * @param array<string, mixed> $payload
   * @return list<string>
   */
  public static function availableThemesFrom(array $payload): array {
    $themeIds = array_map(
      static fn (Theme $theme): string => $theme->getId(),
      self::parseThemes($payload['themes'] ?? []),
    );

    return self::resolveAvailableThemes(self::parseHiddenThemes($payload['hiddenThemes'] ?? []), $themeIds);
  }

  /**
   * @return list<string>
   */
  private function getThemeIds(): array {
    return array_map(static fn (Theme $theme): string => $theme->getId(), $this->themes);
  }

  /**
   * @param list<string> $hiddenThemes
   * @param list<string> $themeIds
   * @return list<string>
   */
  private static function resolveAvailableThemes(array $hiddenThemes, array $themeIds): array {
    $themes = array_unique([...DisplayTheme::values(), ...$themeIds]);

    return array_values(array_diff($themes, $hiddenThemes));
  }

  private static function parseCustomCss(mixed $customCss): string {
    if (!is_string($customCss)) {
      throw new InvalidCustomCssException();
    }

    return $customCss;
  }

  private static function parseDefaultTheme(mixed $defaultTheme): string {
    if (!is_string($defaultTheme)) {
      throw new InvalidDefaultThemeException();
    }

    return $defaultTheme === '' ? self::DEFAULT_THEME : $defaultTheme;
  }

  /**
   * @return list<string>
   */
  private static function parseHiddenThemes(mixed $hiddenThemes): array {
    if (!is_array($hiddenThemes)) {
      throw new InvalidHiddenThemesException();
    }

    $ids = array_filter($hiddenThemes, is_string(...));

    if (count($ids) !== count($hiddenThemes)) {
      throw new InvalidHiddenThemesException();
    }

    return array_values(array_unique($ids));
  }

  /**
   * @return list<Theme>
   */
  private static function parseThemes(mixed $themes): array {
    if (!is_array($themes)) {
      throw new InvalidThemeException();
    }

    $parsed = [];

    foreach ($themes as $theme) {
      if (!is_array($theme)) {
        throw new InvalidThemeException();
      }

      $parsed[] = Theme::fromPayload($theme);
    }

    return $parsed;
  }
}
