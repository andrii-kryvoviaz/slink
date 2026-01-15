<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\User\Domain\ValueObject;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Enum\License;
use Slink\User\Domain\ValueObject\UserPreferences;

final class UserPreferencesTest extends TestCase {
    #[Test]
    public function itCreatesEmptyPreferences(): void {
        $preferences = UserPreferences::empty();

        $this->assertNull($preferences->getDefaultLicense());
    }

    #[Test]
    public function itCreatesPreferencesWithLicense(): void {
        $license = License::CC_BY;
        $preferences = UserPreferences::create($license);

        $this->assertEquals($license, $preferences->getDefaultLicense());
    }

    #[Test]
    public function itCreatesPreferencesWithoutLicense(): void {
        $preferences = UserPreferences::create(null);

        $this->assertNull($preferences->getDefaultLicense());
    }

    #[Test]
    public function itCreatesPreferencesWithDefaultNullLicense(): void {
        $preferences = UserPreferences::create();

        $this->assertNull($preferences->getDefaultLicense());
    }

    #[Test]
    public function itUpdatesDefaultLicense(): void {
        $preferences = UserPreferences::empty();
        $newLicense = License::CC_BY_SA;

        $updated = $preferences->with('defaultLicense', $newLicense);

        $this->assertEquals($newLicense, $updated->getDefaultLicense());
    }

    #[Test]
    public function itUpdatesDefaultLicenseWithStringValue(): void {
        $preferences = UserPreferences::empty();

        $updated = $preferences->with('defaultLicense', 'cc-by-nc');

        $this->assertEquals(License::CC_BY_NC, $updated->getDefaultLicense());
    }

    #[Test]
    public function itUpdatesDefaultLicenseToNull(): void {
        $preferences = UserPreferences::create(License::CC_BY);

        $updated = $preferences->with('defaultLicense', null);

        $this->assertNull($updated->getDefaultLicense());
    }

    #[Test]
    public function itReturnsUnchangedForUnknownKey(): void {
        $license = License::CC0;
        $preferences = UserPreferences::create($license);

        $updated = $preferences->with('unknownKey', 'value');

        $this->assertEquals($license, $updated->getDefaultLicense());
    }

    #[Test]
    public function itConvertsToPayload(): void {
        $license = License::CC_BY_ND;
        $preferences = UserPreferences::create($license);

        $payload = $preferences->toPayload();

        $this->assertArrayHasKey('defaultLicense', $payload);
        $this->assertEquals('cc-by-nd', $payload['defaultLicense']);
    }

    #[Test]
    public function itConvertsToPayloadWithNullLicense(): void {
        $preferences = UserPreferences::empty();

        $payload = $preferences->toPayload();

        $this->assertArrayHasKey('defaultLicense', $payload);
        $this->assertNull($payload['defaultLicense']);
    }

    #[Test]
    public function itCreatesFromPayload(): void {
        $payload = ['defaultLicense' => 'cc-by-sa'];

        $preferences = UserPreferences::fromPayload($payload);

        $this->assertEquals(License::CC_BY_SA, $preferences->getDefaultLicense());
    }

    #[Test]
    public function itCreatesFromPayloadWithNullLicense(): void {
        $payload = ['defaultLicense' => null];

        $preferences = UserPreferences::fromPayload($payload);

        $this->assertNull($preferences->getDefaultLicense());
    }

    #[Test]
    public function itCreatesFromPayloadWithMissingLicense(): void {
        $payload = [];

        $preferences = UserPreferences::fromPayload($payload);

        $this->assertNull($preferences->getDefaultLicense());
    }

    #[Test]
    public function itCreatesFromPayloadWithInvalidLicense(): void {
        $payload = ['defaultLicense' => 'invalid-license'];

        $preferences = UserPreferences::fromPayload($payload);

        $this->assertNull($preferences->getDefaultLicense());
    }

    #[Test]
    public function itIsImmutable(): void {
        $originalLicense = License::CC_BY;
        $preferences = UserPreferences::create($originalLicense);

        $updated = $preferences->with('defaultLicense', License::CC_BY_NC);

        $this->assertEquals($originalLicense, $preferences->getDefaultLicense());
        $this->assertEquals(License::CC_BY_NC, $updated->getDefaultLicense());
        $this->assertNotSame($preferences, $updated);
    }
}
