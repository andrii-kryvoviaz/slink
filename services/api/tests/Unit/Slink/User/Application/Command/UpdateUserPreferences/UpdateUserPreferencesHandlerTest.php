<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\User\Application\Command\UpdateUserPreferences;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Enum\License;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
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
    private ImageRepositoryInterface&MockObject $imageRepository;
    private ImageStoreRepositoryInterface&MockObject $imageStore;
    private UpdateUserPreferencesHandler $handler;

    protected function setUp(): void {
        $this->userStore = $this->createMock(UserStoreRepositoryInterface::class);
        $this->preferencesRepository = $this->createMock(UserPreferencesRepositoryInterface::class);
        $this->imageRepository = $this->createMock(ImageRepositoryInterface::class);
        $this->imageStore = $this->createMock(ImageStoreRepositoryInterface::class);
        
        $this->handler = new UpdateUserPreferencesHandler(
            $this->userStore,
            $this->preferencesRepository,
            $this->imageRepository,
            $this->imageStore
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
        
        $this->imageRepository->expects($this->never())
            ->method('findByUserId');
        
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
        
        $imageView1 = $this->createMock(ImageView::class);
        $imageView1->method('getUuid')->willReturn(ID::generate()->toString());
        $imageView2 = $this->createMock(ImageView::class);
        $imageView2->method('getUuid')->willReturn(ID::generate()->toString());
        
        $this->imageRepository->expects($this->once())
            ->method('findByUserId')
            ->with(ID::fromString($userId))
            ->willReturn([$imageView1, $imageView2]);
        
        $image1 = $this->createMock(Image::class);
        $image1->expects($this->once())
            ->method('updateLicense')
            ->with(License::CC0);
        
        $image2 = $this->createMock(Image::class);
        $image2->expects($this->once())
            ->method('updateLicense')
            ->with(License::CC0);
        
        $this->imageStore->expects($this->exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls($image1, $image2);
        
        $this->imageStore->expects($this->exactly(2))
            ->method('store');
        
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
        
        $this->imageRepository->expects($this->never())
            ->method('findByUserId');
        
        $this->imageStore->expects($this->never())
            ->method('get');
        
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
        
        $imageView = $this->createMock(ImageView::class);
        $imageView->method('getUuid')->willReturn(ID::generate()->toString());
        
        $this->imageRepository->expects($this->once())
            ->method('findByUserId')
            ->willReturn([$imageView]);
        
        $image = $this->createMock(Image::class);
        $image->expects($this->once())
            ->method('updateLicense')
            ->with(null);
        
        $this->imageStore->expects($this->once())
            ->method('get')
            ->willReturn($image);
        
        $this->imageStore->expects($this->once())
            ->method('store')
            ->with($image);
        
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
        
        $this->imageRepository->expects($this->once())
            ->method('findByUserId')
            ->willReturn([]);
        
        $this->imageStore->expects($this->never())
            ->method('get');
        
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
