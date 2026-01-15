<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\User\Application\Command\UpdateUserPreferences;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Enum\License;
use Slink\User\Application\Command\UpdateUserPreferences\UpdateUserPreferencesCommand;
use Slink\User\Domain\ValueObject\UserPreferences;

final class UpdateUserPreferencesCommandTest extends TestCase {
    #[Test]
    public function itCreatesCommandWithDefaultLicense(): void {
        $command = new UpdateUserPreferencesCommand('cc-by');

        $this->assertEquals(License::CC_BY, $command->getDefaultLicense());
        $this->assertFalse($command->shouldSyncLicenseToImages());
    }

    #[Test]
    public function itCreatesCommandWithNullLicense(): void {
        $command = new UpdateUserPreferencesCommand(null);

        $this->assertNull($command->getDefaultLicense());
        $this->assertFalse($command->shouldSyncLicenseToImages());
    }

    #[Test]
    public function itCreatesCommandWithSyncEnabled(): void {
        $command = new UpdateUserPreferencesCommand('cc-by-sa', true);

        $this->assertEquals(License::CC_BY_SA, $command->getDefaultLicense());
        $this->assertTrue($command->shouldSyncLicenseToImages());
    }

    #[Test]
    public function itCreatesCommandWithSyncDisabled(): void {
        $command = new UpdateUserPreferencesCommand('cc0', false);

        $this->assertEquals(License::CC0, $command->getDefaultLicense());
        $this->assertFalse($command->shouldSyncLicenseToImages());
    }

    #[Test]
    public function itReturnsNullForInvalidLicense(): void {
        $command = new UpdateUserPreferencesCommand('invalid-license');

        $this->assertNull($command->getDefaultLicense());
    }

    #[Test]
    public function itReturnsUserPreferences(): void {
        $command = new UpdateUserPreferencesCommand('cc-by-nc');

        $preferences = $command->getPreferences();

        $this->assertInstanceOf(UserPreferences::class, $preferences);
        $this->assertEquals(License::CC_BY_NC, $preferences->getDefaultLicense());
    }

    #[Test]
    public function itReturnsUserPreferencesWithNullLicense(): void {
        $command = new UpdateUserPreferencesCommand(null);

        $preferences = $command->getPreferences();

        $this->assertInstanceOf(UserPreferences::class, $preferences);
        $this->assertNull($preferences->getDefaultLicense());
    }

    #[Test]
    public function itHandlesAllValidLicenseTypes(): void {
        $licenses = [
            'all-rights-reserved',
            'public-domain',
            'cc0',
            'cc-by',
            'cc-by-sa',
            'cc-by-nc',
            'cc-by-nc-sa',
            'cc-by-nd',
            'cc-by-nc-nd',
        ];

        foreach ($licenses as $licenseValue) {
            $command = new UpdateUserPreferencesCommand($licenseValue);
            $license = $command->getDefaultLicense();
            
            $this->assertInstanceOf(License::class, $license);
            $this->assertEquals($licenseValue, $license->value);
        }
    }

    #[Test]
    public function itCreatesSeparatePreferencesInstances(): void {
        $command1 = new UpdateUserPreferencesCommand('cc-by');
        $command2 = new UpdateUserPreferencesCommand('cc-by-sa');

        $prefs1 = $command1->getPreferences();
        $prefs2 = $command2->getPreferences();

        $this->assertNotSame($prefs1, $prefs2);
        $this->assertEquals(License::CC_BY, $prefs1->getDefaultLicense());
        $this->assertEquals(License::CC_BY_SA, $prefs2->getDefaultLicense());
    }
}
