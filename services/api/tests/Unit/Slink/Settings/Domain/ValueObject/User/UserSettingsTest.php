<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Domain\ValueObject\User;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Slink\Settings\Domain\Exception\InvalidAutoRedirectProviderException;
use Slink\Settings\Domain\ValueObject\User\UserSettings;

final class UserSettingsTest extends TestCase {
  private const array BASE_PAYLOAD = [
    'approvalRequired' => false,
    'allowRegistration' => true,
    'password' => ['minLength' => 8, 'requirements' => 0],
  ];

  #[Test]
  public function itDefaultsAutoRedirectProviderIdToNullWhenKeyMissing(): void {
    $settings = UserSettings::fromPayload(self::BASE_PAYLOAD);

    $this->assertNull($settings->getAutoRedirectProviderId());
    $this->assertArrayHasKey('autoRedirectProviderId', $settings->toPayload());
    $this->assertNull($settings->toPayload()['autoRedirectProviderId']);
  }

  #[Test]
  public function itRoundTripsAutoRedirectProviderIdThroughPayload(): void {
    $providerId = Uuid::uuid4()->toString();

    $settings = UserSettings::fromPayload($this->payloadWithProviderId($providerId));

    $this->assertSame($providerId, $settings->getAutoRedirectProviderId());
    $this->assertFalse($settings->isApprovalRequired());
    $this->assertTrue($settings->isAllowRegistration());
    $this->assertSame(['minLength' => 8, 'requirements' => 0], $settings->getPassword()->toPayload());
    $this->assertSame($settings->toPayload(), UserSettings::fromPayload($settings->toPayload())->toPayload());
  }

  #[Test]
  public function itRoundTripsAutoRedirectProviderIdThroughNormalizedPayload(): void {
    $providerId = Uuid::uuid4()->toString();

    $normalized = UserSettings::fromPayload($this->payloadWithProviderId($providerId))->toNormalizedPayload();

    $this->assertSame($providerId, $normalized['user.autoRedirectProviderId']);
    $this->assertSame($providerId, UserSettings::fromNormalizedPayload($normalized, 'user')->getAutoRedirectProviderId());
  }

  #[Test]
  public function itKeepsNullThroughNormalizedPayload(): void {
    $normalized = UserSettings::fromPayload($this->payloadWithProviderId(null))->toNormalizedPayload();

    $this->assertArrayHasKey('user.autoRedirectProviderId', $normalized);
    $this->assertNull($normalized['user.autoRedirectProviderId']);
    $this->assertNull(UserSettings::fromNormalizedPayload($normalized, 'user')->getAutoRedirectProviderId());
  }

  /**
   * @return iterable<string, array{string}>
   */
  public static function blankProviderIdProvider(): iterable {
    yield 'empty string' => [''];
    yield 'whitespace only' => ['   '];
  }

  #[Test]
  #[DataProvider('blankProviderIdProvider')]
  public function itNormalizesBlankAutoRedirectProviderIdToNull(string $providerId): void {
    $settings = UserSettings::fromPayload($this->payloadWithProviderId($providerId));

    $this->assertNull($settings->getAutoRedirectProviderId());
    $this->assertNull($settings->toPayload()['autoRedirectProviderId']);
  }

  #[Test]
  public function itStoresPaddedAutoRedirectProviderIdTrimmed(): void {
    $providerId = Uuid::uuid4()->toString();

    $settings = UserSettings::fromPayload($this->payloadWithProviderId("  {$providerId}  "));

    $this->assertSame($providerId, $settings->getAutoRedirectProviderId());
  }

  /**
   * @return iterable<string, array{mixed}>
   */
  public static function invalidProviderIdProvider(): iterable {
    yield 'not a uuid' => ['not-a-uuid'];
    yield 'numeric string' => ['12345'];
    yield 'uuid one char short' => ['8f14e45f-ceea-467a-9575-0d1e2e3e1a2'];
    yield 'integer' => [123];
    yield 'boolean' => [true];
    yield 'array' => [['8f14e45f-ceea-467a-9575-0d1e2e3e1a2b']];
  }

  #[Test]
  #[DataProvider('invalidProviderIdProvider')]
  public function itRejectsInvalidAutoRedirectProviderId(mixed $providerId): void {
    $this->expectException(InvalidAutoRedirectProviderException::class);

    UserSettings::fromPayload($this->payloadWithProviderId($providerId));
  }

  #[Test]
  public function itThrowsExceptionExposingAutoRedirectProviderIdProperty(): void {
    $this->expectException(InvalidAutoRedirectProviderException::class);

    try {
      UserSettings::fromPayload($this->payloadWithProviderId('not-a-uuid'));
    } catch (InvalidAutoRedirectProviderException $exception) {
      $this->assertSame('user.autoRedirectProviderId', $exception->getProperty());

      throw $exception;
    }
  }

  /**
   * @return array<string, mixed>
   */
  private function payloadWithProviderId(mixed $providerId): array {
    return [...self::BASE_PAYLOAD, 'autoRedirectProviderId' => $providerId];
  }
}
