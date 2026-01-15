<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\LicenseSyncService;
use Slink\Image\Domain\Enum\License;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\ValueObject\ID;

final class LicenseSyncServiceTest extends TestCase {
    private ImageRepositoryInterface&MockObject $imageRepository;
    private ImageStoreRepositoryInterface&MockObject $imageStore;
    private ConfigurationProviderInterface&MockObject $configurationProvider;
    private LicenseSyncService $service;

    protected function setUp(): void {
        $this->imageRepository = $this->createMock(ImageRepositoryInterface::class);
        $this->imageStore = $this->createMock(ImageStoreRepositoryInterface::class);
        $this->configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        
        $this->service = new LicenseSyncService(
            $this->imageRepository,
            $this->imageStore,
            $this->configurationProvider
        );
    }

    #[Test]
    public function itSyncsLicenseToAllUserImages(): void {
        $userId = ID::generate();
        $license = License::CC_BY;
        
        $this->configurationProvider->expects($this->once())
            ->method('get')
            ->with('image.enableLicensing')
            ->willReturn(true);
        
        $imageView1 = $this->createMock(ImageView::class);
        $imageView1->method('getUuid')->willReturn(ID::generate()->toString());
        $imageView2 = $this->createMock(ImageView::class);
        $imageView2->method('getUuid')->willReturn(ID::generate()->toString());
        
        $this->imageRepository->expects($this->once())
            ->method('findByUserId')
            ->with($userId)
            ->willReturn([$imageView1, $imageView2]);
        
        $image1 = $this->createMock(Image::class);
        $image1->expects($this->once())
            ->method('updateLicense')
            ->with($license);
        
        $image2 = $this->createMock(Image::class);
        $image2->expects($this->once())
            ->method('updateLicense')
            ->with($license);
        
        $this->imageStore->expects($this->exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls($image1, $image2);
        
        $this->imageStore->expects($this->exactly(2))
            ->method('store');
        
        $this->service->syncLicenseForUser($userId, $license);
    }

    #[Test]
    public function itDoesNothingWhenLicensingIsDisabled(): void {
        $userId = ID::generate();
        $license = License::CC0;
        
        $this->configurationProvider->expects($this->once())
            ->method('get')
            ->with('image.enableLicensing')
            ->willReturn(false);
        
        $this->imageRepository->expects($this->never())
            ->method('findByUserId');
        
        $this->imageStore->expects($this->never())
            ->method('get');
        
        $this->service->syncLicenseForUser($userId, $license);
    }

    #[Test]
    public function itHandlesUserWithNoImages(): void {
        $userId = ID::generate();
        $license = License::CC_BY_SA;
        
        $this->configurationProvider->expects($this->once())
            ->method('get')
            ->with('image.enableLicensing')
            ->willReturn(true);
        
        $this->imageRepository->expects($this->once())
            ->method('findByUserId')
            ->with($userId)
            ->willReturn([]);
        
        $this->imageStore->expects($this->never())
            ->method('get');
        
        $this->service->syncLicenseForUser($userId, $license);
    }

    #[Test]
    public function itSyncsNullLicenseToImages(): void {
        $userId = ID::generate();
        
        $this->configurationProvider->expects($this->once())
            ->method('get')
            ->with('image.enableLicensing')
            ->willReturn(true);
        
        $imageView = $this->createMock(ImageView::class);
        $imageView->method('getUuid')->willReturn(ID::generate()->toString());
        
        $this->imageRepository->expects($this->once())
            ->method('findByUserId')
            ->with($userId)
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
        
        $this->service->syncLicenseForUser($userId, null);
    }
}
