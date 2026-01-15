<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\User\Application\Command\UpdateUserPreferences;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
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
    private UserStoreRepositoryInterface&MockObject $userStore;
    private UserPreferencesRepositoryInterface&MockObject $preferencesRepository;
    private LicenseSyncServiceInterface&MockObject $licenseSyncService;
    private UpdateUserPreferencesHandler $handler;

    protected function setUp(): void {
        $this->userStore = $this->createMock(UserStoreRepositoryInterface::class);
        $this->preferencesRepository = $this->createMock(UserPreferencesRepositoryInterface::class);
        $this->licenseSyncService = $this->createMock(LicenseSyncServiceInterface::class);
        
        $this->handler = new UpdateUserPreferencesHandler(
            $this->userStore,
            $this->preferencesRepository,
            $this->licenseSyncService
        );
    }

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
        
        $this->userStore->expects($this->once())
            ->method('get')
            ->with(ID::fromString($userId))
            ->willReturn($user);
        
        $this->preferencesRepository->expects($this->once())
            ->method('findByUserId')
            ->with($userId)
            ->willReturn(null);
        
        $this->userStore->expects($this->once())
            ->method('store')
            ->with($user);
        
        $this->licenseSyncService->expects($this->never())
            ->method('syncLicenseForUser');
        
        ($this->handler)($command, $userId);
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
        
        $this->userStore->expects($this->once())
            ->method('get')
            ->with(ID::fromString($userId))
            ->willReturn($user);
        
        $this->preferencesRepository->expects($this->once())
            ->method('findByUserId')
            ->with($userId)
            ->willReturn($preferencesView);
        
        $this->userStore->expects($this->once())
            ->method('store')
            ->with($user);
        
        ($this->handler)($command, $userId);
    }

    #[Test]
    public function itSyncsLicenseToImagesWhenRequested(): void {
        $userId = ID::generate()->toString();
        $command = new UpdateUserPreferencesCommand('cc0', true);
        
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('updatePreferences');
        
        $this->userStore->expects($this->once())
            ->method('get')
            ->with(ID::fromString($userId))
            ->willReturn($user);
        
        $this->preferencesRepository->expects($this->once())
            ->method('findByUserId')
            ->willReturn(null);
        
        $this->userStore->expects($this->once())
            ->method('store')
            ->with($user);
        
        $this->licenseSyncService->expects($this->once())
            ->method('syncLicenseForUser')
            ->with(ID::fromString($userId), License::CC0);
        
        ($this->handler)($command, $userId);
    }

    #[Test]
    public function itDoesNotSyncLicenseWhenNotRequested(): void {
        $userId = ID::generate()->toString();
        $command = new UpdateUserPreferencesCommand('cc-by-nc', false);
        
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('updatePreferences');
        
        $this->userStore->expects($this->once())
            ->method('get')
            ->with(ID::fromString($userId))
            ->willReturn($user);
        
        $this->preferencesRepository->expects($this->once())
            ->method('findByUserId')
            ->willReturn(null);
        
        $this->userStore->expects($this->once())
            ->method('store')
            ->with($user);
        
        $this->licenseSyncService->expects($this->never())
            ->method('syncLicenseForUser');
        
        ($this->handler)($command, $userId);
    }

    #[Test]
    public function itSyncsNullLicenseToImages(): void {
        $userId = ID::generate()->toString();
        $command = new UpdateUserPreferencesCommand(null, true);
        
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('updatePreferences');
        
        $this->userStore->expects($this->once())
            ->method('get')
            ->willReturn($user);
        
        $this->preferencesRepository->expects($this->once())
            ->method('findByUserId')
            ->willReturn(null);
        
        $this->userStore->expects($this->once())
            ->method('store');
        
        $this->licenseSyncService->expects($this->once())
            ->method('syncLicenseForUser')
            ->with(ID::fromString($userId), null);
        
        ($this->handler)($command, $userId);
    }

    #[Test]
    public function itHandlesUserWithNoImages(): void {
        $userId = ID::generate()->toString();
        $command = new UpdateUserPreferencesCommand('cc-by-nd', true);
        
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('updatePreferences');
        
        $this->userStore->expects($this->once())
            ->method('get')
            ->willReturn($user);
        
        $this->preferencesRepository->expects($this->once())
            ->method('findByUserId')
            ->willReturn(null);
        
        $this->userStore->expects($this->once())
            ->method('store');
        
        $this->licenseSyncService->expects($this->once())
            ->method('syncLicenseForUser')
            ->with(ID::fromString($userId), License::CC_BY_ND);
        
        ($this->handler)($command, $userId);
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
        
        $this->userStore->expects($this->once())
            ->method('get')
            ->willReturn($user);
        
        $this->preferencesRepository->expects($this->once())
            ->method('findByUserId')
            ->with($userId)
            ->willReturn($preferencesView);
        
        $this->userStore->expects($this->once())
            ->method('store');
        
        ($this->handler)($command, $userId);
    }
}
