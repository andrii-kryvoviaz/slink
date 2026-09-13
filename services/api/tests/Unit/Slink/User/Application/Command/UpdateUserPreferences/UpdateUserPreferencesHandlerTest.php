<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\User\Application\Command\UpdateUserPreferences;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\LicenseSyncServiceInterface;
use Slink\Image\Domain\Enum\License;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Serializer\ReadonlyObjectDenormalizer;
use Slink\User\Application\Command\UpdateUserPreferences\UpdateUserPreferencesCommand;
use Slink\User\Application\Command\UpdateUserPreferences\UpdateUserPreferencesHandler;
use Slink\User\Domain\Enum\ExifMetadataPreference;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\UserPreferences;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

final class UpdateUserPreferencesHandlerTest extends TestCase {

    #[Test]
    public function itUpdatesUserPreferencesWithoutExistingPreferences(): void {
        $userId = ID::generate()->toString();
        $command = $this->command([
            'license.default' => 'cc-by',
            'image.stripExifMetadataOverride' => 'strip',
        ]);

        $user = $this->createMock(User::class);
        $user->method('getPreferences')->willReturn(UserPreferences::empty());
        $user->expects($this->once())
            ->method('updatePreferences')
            ->with($this->callback(function ($prefs) {
                return $prefs instanceof UserPreferences
                    && $prefs->getDefaultLicense() === License::CC_BY
                    && $prefs->getExifMetadataPreference() === ExifMetadataPreference::Strip;
            }));

        $userStore = $this->createMock(UserStoreRepositoryInterface::class);
        $licenseSyncService = $this->createMock(LicenseSyncServiceInterface::class);

        $userStore->expects($this->once())
            ->method('get')
            ->with(ID::fromString($userId))
            ->willReturn($user);

        $userStore->expects($this->once())
            ->method('store')
            ->with($user);

        $licenseSyncService->expects($this->never())
            ->method('syncLicenseForUser');

        $handler = new UpdateUserPreferencesHandler($userStore, $licenseSyncService, $this->normalizer());
        $handler($command, $userId);
    }

    #[Test]
    public function itUpdatesUserPreferencesWithExistingPreferences(): void {
        $userId = ID::generate()->toString();
        $command = $this->command(['license.default' => 'cc-by-sa']);

        $existingPrefs = UserPreferences::create(License::CC_BY);

        $user = $this->createMock(User::class);
        $user->method('getPreferences')->willReturn($existingPrefs);
        $user->expects($this->once())
            ->method('updatePreferences')
            ->with($this->callback(function ($prefs) {
                return $prefs instanceof UserPreferences
                    && $prefs->getDefaultLicense() === License::CC_BY_SA;
            }));

        $userStore = $this->createMock(UserStoreRepositoryInterface::class);
        $licenseSyncService = $this->createStub(LicenseSyncServiceInterface::class);

        $userStore->expects($this->once())
            ->method('get')
            ->with(ID::fromString($userId))
            ->willReturn($user);

        $userStore->expects($this->once())
            ->method('store')
            ->with($user);

        $handler = new UpdateUserPreferencesHandler($userStore, $licenseSyncService, $this->normalizer());
        $handler($command, $userId);
    }

    #[Test]
    public function itSyncsLicenseToImagesWhenRequested(): void {
        $userId = ID::generate()->toString();
        $command = $this->command([
            'license.default' => 'cc0',
            'license.syncToImages' => true,
        ]);

        $user = $this->createMock(User::class);
        $user->method('getPreferences')->willReturn(UserPreferences::empty());
        $user->expects($this->once())
            ->method('updatePreferences');

        $userStore = $this->createMock(UserStoreRepositoryInterface::class);
        $licenseSyncService = $this->createMock(LicenseSyncServiceInterface::class);

        $userStore->expects($this->once())
            ->method('get')
            ->with(ID::fromString($userId))
            ->willReturn($user);

        $userStore->expects($this->once())
            ->method('store')
            ->with($user);

        $licenseSyncService->expects($this->once())
            ->method('syncLicenseForUser')
            ->with(ID::fromString($userId), License::CC0);

        $handler = new UpdateUserPreferencesHandler($userStore, $licenseSyncService, $this->normalizer());
        $handler($command, $userId);
    }

    #[Test]
    public function itDoesNotSyncLicenseWhenNotRequested(): void {
        $userId = ID::generate()->toString();
        $command = $this->command(['license.default' => 'cc-by-nc']);

        $user = $this->createMock(User::class);
        $user->method('getPreferences')->willReturn(UserPreferences::empty());
        $user->expects($this->once())
            ->method('updatePreferences');

        $userStore = $this->createMock(UserStoreRepositoryInterface::class);
        $licenseSyncService = $this->createMock(LicenseSyncServiceInterface::class);

        $userStore->expects($this->once())
            ->method('get')
            ->with(ID::fromString($userId))
            ->willReturn($user);

        $userStore->expects($this->once())
            ->method('store')
            ->with($user);

        $licenseSyncService->expects($this->never())
            ->method('syncLicenseForUser');

        $handler = new UpdateUserPreferencesHandler($userStore, $licenseSyncService, $this->normalizer());
        $handler($command, $userId);
    }

    #[Test]
    public function itSyncsNullLicenseToImages(): void {
        $userId = ID::generate()->toString();
        $command = $this->command([
            'license.default' => null,
            'license.syncToImages' => true,
        ]);

        $user = $this->createMock(User::class);
        $user->method('getPreferences')->willReturn(UserPreferences::empty());
        $user->expects($this->once())
            ->method('updatePreferences');

        $userStore = $this->createMock(UserStoreRepositoryInterface::class);
        $licenseSyncService = $this->createMock(LicenseSyncServiceInterface::class);

        $userStore->expects($this->once())
            ->method('get')
            ->willReturn($user);

        $userStore->expects($this->once())
            ->method('store');

        $licenseSyncService->expects($this->once())
            ->method('syncLicenseForUser')
            ->with(ID::fromString($userId), null);

        $handler = new UpdateUserPreferencesHandler($userStore, $licenseSyncService, $this->normalizer());
        $handler($command, $userId);
    }

    #[Test]
    public function itHandlesUserWithNoImages(): void {
        $userId = ID::generate()->toString();
        $command = $this->command([
            'license.default' => 'cc-by-nd',
            'license.syncToImages' => true,
        ]);

        $user = $this->createMock(User::class);
        $user->method('getPreferences')->willReturn(UserPreferences::empty());
        $user->expects($this->once())
            ->method('updatePreferences');

        $userStore = $this->createMock(UserStoreRepositoryInterface::class);
        $licenseSyncService = $this->createMock(LicenseSyncServiceInterface::class);

        $userStore->expects($this->once())
            ->method('get')
            ->willReturn($user);

        $userStore->expects($this->once())
            ->method('store');

        $licenseSyncService->expects($this->once())
            ->method('syncLicenseForUser')
            ->with(ID::fromString($userId), License::CC_BY_ND);

        $handler = new UpdateUserPreferencesHandler($userStore, $licenseSyncService, $this->normalizer());
        $handler($command, $userId);
    }

    #[Test]
    public function itPreservesExistingPreferencesWhenUpdating(): void {
        $userId = ID::generate()->toString();
        $oldLicense = License::AllRightsReserved;
        $newLicense = License::PublicDomain;

        $command = $this->command(['license.default' => $newLicense->value]);

        $existingPrefs = UserPreferences::create($oldLicense);

        $user = $this->createMock(User::class);
        $user->method('getPreferences')->willReturn($existingPrefs);
        $user->expects($this->once())
            ->method('updatePreferences')
            ->with($this->callback(function ($prefs) use ($newLicense) {
                return $prefs instanceof UserPreferences
                    && $prefs->getDefaultLicense() === $newLicense;
            }));

        $userStore = $this->createMock(UserStoreRepositoryInterface::class);
        $licenseSyncService = $this->createStub(LicenseSyncServiceInterface::class);

        $userStore->expects($this->once())
            ->method('get')
            ->willReturn($user);

        $userStore->expects($this->once())
            ->method('store');

        $handler = new UpdateUserPreferencesHandler($userStore, $licenseSyncService, $this->normalizer());
        $handler($command, $userId);
    }

    #[Test]
    public function itExcludesSyncTriggerFromTheChangeSetAndRespectsMergePatch(): void {
        $userId = ID::generate()->toString();
        $existingPrefs = UserPreferences::fromPayload([
            'license.default' => 'cc-by',
            'display.theme' => 'nord',
        ]);

        $command = $this->command([
            'display.theme' => null,
            'license.syncToImages' => true,
        ]);

        $user = $this->createMock(User::class);
        $user->method('getPreferences')->willReturn($existingPrefs);
        $user->expects($this->once())
            ->method('updatePreferences')
            ->with($this->callback(function ($prefs) {
                return $prefs instanceof UserPreferences
                    && $prefs->toPayload() === [
                        'license.default' => 'cc-by',
                        'display.theme' => null,
                    ];
            }));

        $userStore = $this->createMock(UserStoreRepositoryInterface::class);
        $licenseSyncService = $this->createMock(LicenseSyncServiceInterface::class);

        $userStore->expects($this->once())
            ->method('get')
            ->willReturn($user);

        $userStore->expects($this->once())
            ->method('store')
            ->with($user);

        $licenseSyncService->expects($this->once())
            ->method('syncLicenseForUser')
            ->with(ID::fromString($userId), License::CC_BY);

        $handler = new UpdateUserPreferencesHandler($userStore, $licenseSyncService, $this->normalizer());
        $handler($command, $userId);
    }

    #[Test]
    public function itSyncsTheStoredLicenseWhenOnlyTheTriggerIsSent(): void {
        $userId = ID::generate()->toString();
        $storedPrefs = UserPreferences::create(License::CC_BY);

        $command = $this->command(['license.syncToImages' => true]);

        $user = $this->createMock(User::class);
        $user->method('getPreferences')->willReturn($storedPrefs);
        $user->expects($this->once())
            ->method('updatePreferences')
            ->with($this->callback(function ($prefs) {
                return $prefs instanceof UserPreferences
                    && $prefs->getDefaultLicense() === License::CC_BY;
            }));

        $userStore = $this->createMock(UserStoreRepositoryInterface::class);
        $licenseSyncService = $this->createMock(LicenseSyncServiceInterface::class);

        $userStore->expects($this->once())
            ->method('get')
            ->willReturn($user);

        $userStore->expects($this->once())
            ->method('store')
            ->with($user);

        $licenseSyncService->expects($this->once())
            ->method('syncLicenseForUser')
            ->with(ID::fromString($userId), License::CC_BY);

        $handler = new UpdateUserPreferencesHandler($userStore, $licenseSyncService, $this->normalizer());
        $handler($command, $userId);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function command(array $body): UpdateUserPreferencesCommand {
        return $this->denormalizer()->denormalize($body, UpdateUserPreferencesCommand::class);
    }

    private function denormalizer(): ReadonlyObjectDenormalizer {
        $metadata = new ClassMetadataFactory(new AttributeLoader());

        return new ReadonlyObjectDenormalizer(new PropertyNormalizer($metadata, new MetadataAwareNameConverter($metadata)));
    }

    private function normalizer(): NormalizerInterface {
        $metadata = new ClassMetadataFactory(new AttributeLoader());

        return new Serializer([new ObjectNormalizer($metadata, new MetadataAwareNameConverter($metadata))]);
    }
}
