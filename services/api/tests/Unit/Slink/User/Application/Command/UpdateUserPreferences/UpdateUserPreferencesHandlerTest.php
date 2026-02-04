<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\User\Application\Command\UpdateUserPreferences;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\LicenseSyncServiceInterface;
use Slink\Image\Domain\Enum\License;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Application\Command\UpdateUserPreferences\UpdateUserPreferencesCommand;
use Slink\User\Application\Command\UpdateUserPreferences\UpdateUserPreferencesHandler;
use Slink\User\Domain\Repository\UserPreferencesRepositoryInterface;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\UserPreferences;
use Slink\User\Infrastructure\ReadModel\View\UserPreferencesView;

final class UpdateUserPreferencesHandlerTest extends TestCase {

    #[Test]
    public function itUpdatesUserPreferencesWithoutExistingPreferences(): void {
        $userId = ID::generate()->toString();
        $command = new UpdateUserPreferencesCommand('cc-by');

        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('updatePreferences')
            ->with($this->callback(function ($prefs) {
                return $prefs instanceof UserPreferences
                    && $prefs->getDefaultLicense() === License::CC_BY;
            }));

        $userStore = $this->createMock(UserStoreRepositoryInterface::class);
        $preferencesRepository = $this->createMock(UserPreferencesRepositoryInterface::class);
        $licenseSyncService = $this->createMock(LicenseSyncServiceInterface::class);

        $userStore->expects($this->once())
            ->method('get')
            ->with(ID::fromString($userId))
            ->willReturn($user);

        $preferencesRepository->expects($this->once())
            ->method('findByUserId')
            ->with($userId)
            ->willReturn(null);

        $userStore->expects($this->once())
            ->method('store')
            ->with($user);

        $licenseSyncService->expects($this->never())
            ->method('syncLicenseForUser');

        $handler = new UpdateUserPreferencesHandler($userStore, $preferencesRepository, $licenseSyncService);
        $handler($command, $userId);
    }

    #[Test]
    public function itUpdatesUserPreferencesWithExistingPreferences(): void {
        $userId = ID::generate()->toString();
        $command = new UpdateUserPreferencesCommand('cc-by-sa');

        $existingPrefs = UserPreferences::create(License::CC_BY);
        $preferencesView = $this->createMock(UserPreferencesView::class);
        $preferencesView->expects($this->once())
            ->method('getPreferences')
            ->willReturn($existingPrefs);

        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('updatePreferences')
            ->with($this->callback(function ($prefs) {
                return $prefs instanceof UserPreferences
                    && $prefs->getDefaultLicense() === License::CC_BY_SA;
            }));

        $userStore = $this->createMock(UserStoreRepositoryInterface::class);
        $preferencesRepository = $this->createMock(UserPreferencesRepositoryInterface::class);
        $licenseSyncService = $this->createStub(LicenseSyncServiceInterface::class);

        $userStore->expects($this->once())
            ->method('get')
            ->with(ID::fromString($userId))
            ->willReturn($user);

        $preferencesRepository->expects($this->once())
            ->method('findByUserId')
            ->with($userId)
            ->willReturn($preferencesView);

        $userStore->expects($this->once())
            ->method('store')
            ->with($user);

        $handler = new UpdateUserPreferencesHandler($userStore, $preferencesRepository, $licenseSyncService);
        $handler($command, $userId);
    }

    #[Test]
    public function itSyncsLicenseToImagesWhenRequested(): void {
        $userId = ID::generate()->toString();
        $command = new UpdateUserPreferencesCommand('cc0', true);

        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('updatePreferences');

        $userStore = $this->createMock(UserStoreRepositoryInterface::class);
        $preferencesRepository = $this->createMock(UserPreferencesRepositoryInterface::class);
        $licenseSyncService = $this->createMock(LicenseSyncServiceInterface::class);

        $userStore->expects($this->once())
            ->method('get')
            ->with(ID::fromString($userId))
            ->willReturn($user);

        $preferencesRepository->expects($this->once())
            ->method('findByUserId')
            ->willReturn(null);

        $userStore->expects($this->once())
            ->method('store')
            ->with($user);

        $licenseSyncService->expects($this->once())
            ->method('syncLicenseForUser')
            ->with(ID::fromString($userId), License::CC0);

        $handler = new UpdateUserPreferencesHandler($userStore, $preferencesRepository, $licenseSyncService);
        $handler($command, $userId);
    }

    #[Test]
    public function itDoesNotSyncLicenseWhenNotRequested(): void {
        $userId = ID::generate()->toString();
        $command = new UpdateUserPreferencesCommand('cc-by-nc', false);

        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('updatePreferences');

        $userStore = $this->createMock(UserStoreRepositoryInterface::class);
        $preferencesRepository = $this->createMock(UserPreferencesRepositoryInterface::class);
        $licenseSyncService = $this->createMock(LicenseSyncServiceInterface::class);

        $userStore->expects($this->once())
            ->method('get')
            ->with(ID::fromString($userId))
            ->willReturn($user);

        $preferencesRepository->expects($this->once())
            ->method('findByUserId')
            ->willReturn(null);

        $userStore->expects($this->once())
            ->method('store')
            ->with($user);

        $licenseSyncService->expects($this->never())
            ->method('syncLicenseForUser');

        $handler = new UpdateUserPreferencesHandler($userStore, $preferencesRepository, $licenseSyncService);
        $handler($command, $userId);
    }

    #[Test]
    public function itSyncsNullLicenseToImages(): void {
        $userId = ID::generate()->toString();
        $command = new UpdateUserPreferencesCommand(null, true);

        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('updatePreferences');

        $userStore = $this->createMock(UserStoreRepositoryInterface::class);
        $preferencesRepository = $this->createMock(UserPreferencesRepositoryInterface::class);
        $licenseSyncService = $this->createMock(LicenseSyncServiceInterface::class);

        $userStore->expects($this->once())
            ->method('get')
            ->willReturn($user);

        $preferencesRepository->expects($this->once())
            ->method('findByUserId')
            ->willReturn(null);

        $userStore->expects($this->once())
            ->method('store');

        $licenseSyncService->expects($this->once())
            ->method('syncLicenseForUser')
            ->with(ID::fromString($userId), null);

        $handler = new UpdateUserPreferencesHandler($userStore, $preferencesRepository, $licenseSyncService);
        $handler($command, $userId);
    }

    #[Test]
    public function itHandlesUserWithNoImages(): void {
        $userId = ID::generate()->toString();
        $command = new UpdateUserPreferencesCommand('cc-by-nd', true);

        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('updatePreferences');

        $userStore = $this->createMock(UserStoreRepositoryInterface::class);
        $preferencesRepository = $this->createMock(UserPreferencesRepositoryInterface::class);
        $licenseSyncService = $this->createMock(LicenseSyncServiceInterface::class);

        $userStore->expects($this->once())
            ->method('get')
            ->willReturn($user);

        $preferencesRepository->expects($this->once())
            ->method('findByUserId')
            ->willReturn(null);

        $userStore->expects($this->once())
            ->method('store');

        $licenseSyncService->expects($this->once())
            ->method('syncLicenseForUser')
            ->with(ID::fromString($userId), License::CC_BY_ND);

        $handler = new UpdateUserPreferencesHandler($userStore, $preferencesRepository, $licenseSyncService);
        $handler($command, $userId);
    }

    #[Test]
    public function itPreservesExistingPreferencesWhenUpdating(): void {
        $userId = ID::generate()->toString();
        $oldLicense = License::AllRightsReserved;
        $newLicense = License::PublicDomain;

        $command = new UpdateUserPreferencesCommand($newLicense->value);

        $existingPrefs = UserPreferences::create($oldLicense);
        $preferencesView = $this->createMock(UserPreferencesView::class);
        $preferencesView->expects($this->once())
            ->method('getPreferences')
            ->willReturn($existingPrefs);

        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('updatePreferences')
            ->with($this->callback(function ($prefs) use ($newLicense) {
                return $prefs instanceof UserPreferences
                    && $prefs->getDefaultLicense() === $newLicense;
            }));

        $userStore = $this->createMock(UserStoreRepositoryInterface::class);
        $preferencesRepository = $this->createMock(UserPreferencesRepositoryInterface::class);
        $licenseSyncService = $this->createStub(LicenseSyncServiceInterface::class);

        $userStore->expects($this->once())
            ->method('get')
            ->willReturn($user);

        $preferencesRepository->expects($this->once())
            ->method('findByUserId')
            ->with($userId)
            ->willReturn($preferencesView);

        $userStore->expects($this->once())
            ->method('store');

        $handler = new UpdateUserPreferencesHandler($userStore, $preferencesRepository, $licenseSyncService);
        $handler($command, $userId);
    }
}
