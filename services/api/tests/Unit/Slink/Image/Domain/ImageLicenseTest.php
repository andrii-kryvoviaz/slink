<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Enum\License;
use Slink\Image\Domain\Event\ImageLicenseWasUpdated;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Shared\Domain\ValueObject\ID;

final class ImageLicenseTest extends TestCase {
    #[Test]
    public function itCreatesImageWithLicense(): void {
        $id = ID::generate();
        $userId = ID::generate();
        $attributes = ImageAttributes::create('test.jpg', '', true);
        $metadata = new ImageMetadata(1024, 'image/jpeg', 800, 600);
        $license = License::CC_BY;

        $image = Image::create($id, $userId, $attributes, $metadata, null, null, $license);

        $events = $image->releaseEvents();
        $this->assertCount(1, $events);
    }

    #[Test]
    public function itCreatesImageWithoutLicense(): void {
        $id = ID::generate();
        $userId = ID::generate();
        $attributes = ImageAttributes::create('test.jpg', '', true);
        $metadata = new ImageMetadata(1024, 'image/jpeg', 800, 600);

        $image = Image::create($id, $userId, $attributes, $metadata, null, null, null);

        $events = $image->releaseEvents();
        $this->assertCount(1, $events);
    }

    #[Test]
    public function itUpdatesLicense(): void {
        $image = $this->createTestImage();
        $license = License::CC_BY_SA;

        $image->updateLicense($license);

        $events = $image->releaseEvents();
        $licenseEvent = array_filter($events, fn($e) => $e instanceof ImageLicenseWasUpdated);
        $this->assertCount(1, $licenseEvent);
    }

    #[Test]
    public function itUpdatesLicenseToNull(): void {
        $image = $this->createTestImage(License::CC_BY);

        $image->updateLicense(null);

        $events = $image->releaseEvents();
        $licenseEvent = array_filter($events, fn($e) => $e instanceof ImageLicenseWasUpdated);
        $this->assertCount(1, $licenseEvent);
    }

    #[Test]
    public function itUpdatesLicenseMultipleTimes(): void {
        $image = $this->createTestImage();

        $image->updateLicense(License::CC_BY);
        $image->updateLicense(License::CC_BY_SA);
        $image->updateLicense(License::CC0);

        $events = $image->releaseEvents();
        $licenseEvents = array_filter($events, fn($e) => $e instanceof ImageLicenseWasUpdated);
        $this->assertCount(3, $licenseEvents);
    }

    #[Test]
    public function itUpdatesFromOneLicenseToAnother(): void {
        $image = $this->createTestImage(License::AllRightsReserved);
        
        $image->updateLicense(License::PublicDomain);

        $events = $image->releaseEvents();
        $licenseEvents = array_filter($events, fn($e) => $e instanceof ImageLicenseWasUpdated);
        $this->assertCount(1, $licenseEvents);
        
        $event = array_values($licenseEvents)[0];
        $this->assertEquals(License::PublicDomain, $event->license);
    }

    #[Test]
    public function itCanUpdateLicenseForAllLicenseTypes(): void {
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
            $image = $this->createTestImage();
            
            $image->updateLicense($license);

            $events = $image->releaseEvents();
            $licenseEvents = array_filter($events, fn($e) => $e instanceof ImageLicenseWasUpdated);
            $this->assertCount(1, $licenseEvents);
            
            $event = array_values($licenseEvents)[0];
            $this->assertEquals($license, $event->license);
        }
    }

    private function createTestImage(?License $initialLicense = null): Image {
        $id = ID::generate();
        $userId = ID::generate();
        $attributes = ImageAttributes::create('test.jpg', '', true);
        $metadata = new ImageMetadata(1024, 'image/jpeg', 800, 600);

        $image = Image::create($id, $userId, $attributes, $metadata, null, null, $initialLicense);
        $image->releaseEvents();

        return $image;
    }
}
