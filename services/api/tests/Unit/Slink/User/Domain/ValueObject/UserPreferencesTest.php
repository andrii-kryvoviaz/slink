<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\User\Domain\ValueObject;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Enum\License;
use Slink\User\Domain\Enum\LandingPage;
use Slink\User\Domain\ValueObject\UserPreferences;

final class UserPreferencesTest extends TestCase {
    #[Test]
    public function itCreatesEmptyPreferences(): void {
        $preferences = UserPreferences::empty();

        $this->assertNull($preferences->getDefaultLicense());
        $this->assertNull($preferences->getDefaultLandingPage());
    }

    #[Test]
    public function itCreatesPreferencesWithLicense(): void {
        $license = License::CC_BY;
        $preferences = UserPreferences::create($license);

        $this->assertEquals($license, $preferences->getDefaultLicense());
    }

    #[Test]
    public function itCreatesPreferencesWithLandingPage(): void {
        $landingPage = LandingPage::Upload;
        $preferences = UserPreferences::create(defaultLandingPage: $landingPage);

        $this->assertEquals($landingPage, $preferences->getDefaultLandingPage());
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

        $updated = $preferences->withDefaultLicense($newLicense);

        $this->assertEquals($newLicense, $updated->getDefaultLicense());
    }

    #[Test]
    public function itUpdatesDefaultLandingPage(): void {
        $preferences = UserPreferences::empty();
        $landingPage = LandingPage::Upload;

        $updated = $preferences->withDefaultLandingPage($landingPage);

        $this->assertEquals($landingPage, $updated->getDefaultLandingPage());
    }

    #[Test]
    public function itUpdatesDefaultLicenseToNull(): void {
        $preferences = UserPreferences::create(License::CC_BY);

        $updated = $preferences->withDefaultLicense(null);

        $this->assertNull($updated->getDefaultLicense());
    }

    #[Test]
    public function itAppliesChanges(): void {
        $license = License::CC0;
        $preferences = UserPreferences::create($license);

        $updated = $preferences->applyChanges(['navigation.landingPage' => 'upload']);

        $this->assertEquals($license, $updated->getDefaultLicense());
        $this->assertEquals(LandingPage::Upload, $updated->getDefaultLandingPage());
    }

    #[Test]
    public function itConvertsToPayload(): void {
        $license = License::CC_BY_ND;
        $preferences = UserPreferences::create($license);

        $payload = $preferences->toPayload();

        $this->assertArrayHasKey('license.default', $payload);
        $this->assertEquals('cc-by-nd', $payload['license.default']);
    }

    #[Test]
    public function itConvertsToPayloadWithNullLicense(): void {
        $preferences = UserPreferences::empty();

        $payload = $preferences->toPayload();

        $this->assertEmpty($payload);
    }

    #[Test]
    public function itCreatesFromPayload(): void {
        $payload = ['license.default' => 'cc-by-sa'];

        $preferences = UserPreferences::fromPayload($payload);

        $this->assertEquals(License::CC_BY_SA, $preferences->getDefaultLicense());
    }

    #[Test]
    public function itCreatesFromPayloadWithLandingPage(): void {
        $payload = ['navigation.landingPage' => 'upload'];

        $preferences = UserPreferences::fromPayload($payload);

        $this->assertEquals(LandingPage::Upload, $preferences->getDefaultLandingPage());
    }

    #[Test]
    public function itCreatesFromPayloadWithNullLicense(): void {
        $payload = ['license.default' => null];

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
        $payload = ['license.default' => 'invalid-license'];

        $preferences = UserPreferences::fromPayload($payload);

        $this->assertNull($preferences->getDefaultLicense());
    }

    #[Test]
    public function itIsImmutable(): void {
        $originalLicense = License::CC_BY;
        $preferences = UserPreferences::create($originalLicense);

        $updated = $preferences->withDefaultLicense(License::CC_BY_NC);

        $this->assertEquals($originalLicense, $preferences->getDefaultLicense());
        $this->assertEquals(License::CC_BY_NC, $updated->getDefaultLicense());
        $this->assertNotSame($preferences, $updated);
    }

    #[Test]
    public function itDefaultsExternalUploadAutoPublishToFalseWhenCreatedWithoutArg(): void {
        $preferences = UserPreferences::create();

        $this->assertFalse($preferences->getExternalUploadAutoPublish());
    }

    #[Test]
    public function itDefaultsExternalUploadAutoPublishToFalseFromEmptyPayload(): void {
        $preferences = UserPreferences::fromPayload([]);

        $this->assertFalse($preferences->getExternalUploadAutoPublish());
    }

    #[Test]
    public function itRoundTripsExternalUploadAutoPublishThroughPayload(): void {
        $preferences = UserPreferences::create(externalUploadAutoPublish: true);

        $payload = $preferences->toPayload();

        $this->assertArrayHasKey('image.externalUploadAutoPublish', $payload);
        $this->assertTrue($payload['image.externalUploadAutoPublish']);

        $restored = UserPreferences::fromPayload($payload);

        $this->assertTrue($restored->getExternalUploadAutoPublish());
    }

    #[Test]
    public function itUpdatesExternalUploadAutoPublishImmutably(): void {
        $preferences = UserPreferences::create(externalUploadAutoPublish: false);

        $updated = $preferences->withExternalUploadAutoPublish(true);

        $this->assertFalse($preferences->getExternalUploadAutoPublish());
        $this->assertTrue($updated->getExternalUploadAutoPublish());
        $this->assertNotSame($preferences, $updated);
    }

    #[Test]
    public function itAppliesExternalUploadAutoPublishChange(): void {
        $preferences = UserPreferences::create(externalUploadAutoPublish: false);

        $updated = $preferences->applyChanges(['image.externalUploadAutoPublish' => true]);

        $this->assertTrue($updated->getExternalUploadAutoPublish());
    }

    #[Test]
    public function itDoesNotClearExternalUploadAutoPublishWhenNullApplied(): void {
        $preferences = UserPreferences::create(externalUploadAutoPublish: true);

        $updated = $preferences->applyChanges(['image.externalUploadAutoPublish' => null]);

        $this->assertTrue($updated->getExternalUploadAutoPublish());
    }

    #[Test]
    public function itDefaultsAutoGroupBatchUploadsToTrueWhenCreatedWithoutArg(): void {
        $preferences = UserPreferences::create();

        $this->assertTrue($preferences->getAutoGroupBatchUploads());
    }

    #[Test]
    public function itDefaultsAutoGroupBatchUploadsToTrueFromEmptyPayload(): void {
        $preferences = UserPreferences::fromPayload([]);

        $this->assertTrue($preferences->getAutoGroupBatchUploads());
    }

    #[Test]
    public function itRoundTripsAutoGroupBatchUploadsFalseThroughPayload(): void {
        $preferences = UserPreferences::create(autoGroupBatchUploads: false);

        $payload = $preferences->toPayload();

        $this->assertArrayHasKey('image.autoGroupBatchUploads', $payload);
        $this->assertFalse($payload['image.autoGroupBatchUploads']);

        $restored = UserPreferences::fromPayload($payload);

        $this->assertFalse($restored->getAutoGroupBatchUploads());
    }

    #[Test]
    public function itRoundTripsAutoGroupBatchUploadsTrueThroughPayload(): void {
        $preferences = UserPreferences::create(autoGroupBatchUploads: true);

        $payload = $preferences->toPayload();

        $this->assertArrayHasKey('image.autoGroupBatchUploads', $payload);
        $this->assertTrue($payload['image.autoGroupBatchUploads']);

        $restored = UserPreferences::fromPayload($payload);

        $this->assertTrue($restored->getAutoGroupBatchUploads());
    }

    #[Test]
    public function itUpdatesAutoGroupBatchUploadsImmutably(): void {
        $preferences = UserPreferences::create(autoGroupBatchUploads: true);

        $updated = $preferences->withAutoGroupBatchUploads(false);

        $this->assertTrue($preferences->getAutoGroupBatchUploads());
        $this->assertFalse($updated->getAutoGroupBatchUploads());
        $this->assertNotSame($preferences, $updated);
    }

    #[Test]
    public function itAppliesAutoGroupBatchUploadsChange(): void {
        $preferences = UserPreferences::create(autoGroupBatchUploads: true);

        $updated = $preferences->applyChanges(['image.autoGroupBatchUploads' => false]);

        $this->assertFalse($updated->getAutoGroupBatchUploads());
    }

    #[Test]
    public function itDoesNotClearAutoGroupBatchUploadsWhenNullApplied(): void {
        $preferences = UserPreferences::create(autoGroupBatchUploads: false);

        $updated = $preferences->applyChanges(['image.autoGroupBatchUploads' => null]);

        $this->assertFalse($updated->getAutoGroupBatchUploads());
    }
}
