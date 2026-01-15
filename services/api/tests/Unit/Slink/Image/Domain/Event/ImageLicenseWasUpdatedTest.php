<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Domain\Event;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Enum\License;
use Slink\Image\Domain\Event\ImageLicenseWasUpdated;
use Slink\Shared\Domain\ValueObject\ID;

final class ImageLicenseWasUpdatedTest extends TestCase {
    #[Test]
    public function itCreatesEventWithLicense(): void {
        $id = ID::generate();
        $license = License::CC_BY;

        $event = new ImageLicenseWasUpdated($id, $license);

        $this->assertEquals($id, $event->id);
        $this->assertEquals($license, $event->license);
    }

    #[Test]
    public function itCreatesEventWithNullLicense(): void {
        $id = ID::generate();

        $event = new ImageLicenseWasUpdated($id, null);

        $this->assertEquals($id, $event->id);
        $this->assertNull($event->license);
    }

    #[Test]
    public function itConvertsToPayload(): void {
        $id = ID::generate();
        $license = License::CC_BY_SA;
        $event = new ImageLicenseWasUpdated($id, $license);

        $payload = $event->toPayload();

        $this->assertArrayHasKey('uuid', $payload);
        $this->assertArrayHasKey('license', $payload);
        $this->assertEquals($id->toString(), $payload['uuid']);
        $this->assertEquals('cc-by-sa', $payload['license']);
    }

    #[Test]
    public function itConvertsToPayloadWithNullLicense(): void {
        $id = ID::generate();
        $event = new ImageLicenseWasUpdated($id, null);

        $payload = $event->toPayload();

        $this->assertArrayHasKey('uuid', $payload);
        $this->assertArrayHasKey('license', $payload);
        $this->assertEquals($id->toString(), $payload['uuid']);
        $this->assertNull($payload['license']);
    }

    #[Test]
    public function itCreatesFromPayload(): void {
        $uuid = ID::generate()->toString();
        $payload = [
            'uuid' => $uuid,
            'license' => 'cc-by-nc',
        ];

        $event = ImageLicenseWasUpdated::fromPayload($payload);

        $this->assertEquals($uuid, $event->id->toString());
        $this->assertEquals(License::CC_BY_NC, $event->license);
    }

    #[Test]
    public function itCreatesFromPayloadWithNullLicense(): void {
        $uuid = ID::generate()->toString();
        $payload = [
            'uuid' => $uuid,
            'license' => null,
        ];

        $event = ImageLicenseWasUpdated::fromPayload($payload);

        $this->assertEquals($uuid, $event->id->toString());
        $this->assertNull($event->license);
    }

    #[Test]
    public function itCreatesFromPayloadWithMissingLicense(): void {
        $uuid = ID::generate()->toString();
        $payload = [
            'uuid' => $uuid,
        ];

        $event = ImageLicenseWasUpdated::fromPayload($payload);

        $this->assertEquals($uuid, $event->id->toString());
        $this->assertNull($event->license);
    }

    #[Test]
    public function itHandlesInvalidLicenseGracefully(): void {
        $uuid = ID::generate()->toString();
        $payload = [
            'uuid' => $uuid,
            'license' => 'invalid-license',
        ];

        $event = ImageLicenseWasUpdated::fromPayload($payload);

        $this->assertEquals($uuid, $event->id->toString());
        $this->assertNull($event->license);
    }

    #[Test]
    public function itRoundTripsWithAllLicenseTypes(): void {
        $licenses = [
            License::AllRightsReserved,
            License::PublicDomain,
            License::CC0,
            License::CC_BY,
            License::CC_BY_SA,
            License::CC_BY_NC,
            License::CC_BY_NC_SA,
            License::CC_BY_ND,
            License::CC_BY_NC_ND,
        ];

        foreach ($licenses as $license) {
            $id = ID::generate();
            $originalEvent = new ImageLicenseWasUpdated($id, $license);
            
            $payload = $originalEvent->toPayload();
            $recreatedEvent = ImageLicenseWasUpdated::fromPayload($payload);

            $this->assertEquals($id->toString(), $recreatedEvent->id->toString());
            $this->assertEquals($license, $recreatedEvent->license);
        }
    }

    #[Test]
    public function itRoundTripsWithNullLicense(): void {
        $id = ID::generate();
        $originalEvent = new ImageLicenseWasUpdated($id, null);
        
        $payload = $originalEvent->toPayload();
        $recreatedEvent = ImageLicenseWasUpdated::fromPayload($payload);

        $this->assertEquals($id->toString(), $recreatedEvent->id->toString());
        $this->assertNull($recreatedEvent->license);
    }

    #[Test]
    public function itHandlesNonStringLicenseInPayload(): void {
        $uuid = ID::generate()->toString();
        $payload = [
            'uuid' => $uuid,
            'license' => 123,
        ];

        $event = ImageLicenseWasUpdated::fromPayload($payload);

        $this->assertNull($event->license);
    }
}
