<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Domain\ValueObject\Customization;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Exception\InvalidLogoUrlException;
use Slink\Settings\Domain\Exception\InvalidSiteDescriptionException;
use Slink\Settings\Domain\Exception\InvalidSiteNameException;
use Slink\Settings\Domain\ValueObject\Customization\CustomizationSettings;

final class CustomizationSettingsTest extends TestCase {
  #[Test]
  public function itRoundTripsPayload(): void {
    $payload = [
      'siteName' => 'My Site',
      'siteDescription' => 'My description',
      'logoUrl' => 'https://example.com/logo.png',
    ];

    $settings = CustomizationSettings::fromPayload($payload);

    $this->assertSame('My Site', $settings->getSiteName());
    $this->assertSame('My description', $settings->getSiteDescription());
    $this->assertSame('https://example.com/logo.png', $settings->getLogoUrl());
    $this->assertSame($payload, $settings->toPayload());
  }

  #[Test]
  public function itAppliesDefaultsOnEmptyPayload(): void {
    $settings = CustomizationSettings::fromPayload([]);

    $this->assertSame(CustomizationSettings::DEFAULT_SITE_NAME, $settings->getSiteName());
    $this->assertSame(CustomizationSettings::DEFAULT_SITE_DESCRIPTION, $settings->getSiteDescription());
    $this->assertSame('', $settings->getLogoUrl());
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
