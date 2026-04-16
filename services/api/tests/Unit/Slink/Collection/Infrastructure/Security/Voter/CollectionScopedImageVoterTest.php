<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Infrastructure\Security\Voter;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionItemView;
use Slink\Collection\Infrastructure\Security\Voter\CollectionScopedImageVoter;
use Slink\Image\Domain\Enum\ImageAccess;
use Slink\Image\Domain\Service\ImageUrlSignatureInterface;
use Slink\Image\Domain\ValueObject\ImageAccessContext;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class CollectionScopedImageVoterTest extends TestCase {
  private ImageUrlSignatureInterface&Stub $signature;
  private ShareRepositoryInterface&Stub $shareRepository;
  private CollectionItemRepositoryInterface&Stub $collectionItemRepository;

  protected function setUp(): void {
    parent::setUp();

    $this->signature = $this->createStub(ImageUrlSignatureInterface::class);
    $this->shareRepository = $this->createStub(ShareRepositoryInterface::class);
    $this->collectionItemRepository = $this->createStub(CollectionItemRepositoryInterface::class);
  }

  private function createVoter(): CollectionScopedImageVoter {
    return new CollectionScopedImageVoter(
      $this->signature,
      $this->shareRepository,
      $this->collectionItemRepository,
    );
  }

  private function createToken(): TokenInterface&Stub {
    $token = $this->createStub(TokenInterface::class);
    $token->method('getUserIdentifier')->willReturn('');

    return $token;
  }

  private function createContext(
    string $imageId,
    ?string $scopeCollectionId = null,
    ?string $scopeSignature = null,
  ): ImageAccessContext {
    $image = $this->createStub(ImageView::class);
    $image->method('getUuid')->willReturn($imageId);

    return new ImageAccessContext($image, $scopeCollectionId, $scopeSignature);
  }

  #[Test]
  public function itAbstainsForUnsupportedAttribute(): void {
    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'col', 'sig');

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::Edit]);

    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
  }

  #[Test]
  public function itAbstainsForUnsupportedSubject(): void {
    $voter = $this->createVoter();

    $result = $voter->vote($this->createToken(), new \stdClass(), [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
  }

  #[Test]
  public function itDeniesWhenCollectionParamMissing(): void {
    $voter = $this->createVoter();
    $context = $this->createContext('image-id', null, 'sig');

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesWhenCollectionParamEmpty(): void {
    $voter = $this->createVoter();
    $context = $this->createContext('image-id', '', 'sig');

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesWhenSignatureParamMissing(): void {
    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', null);

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesWhenSignatureParamEmpty(): void {
    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', '');

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesWhenSignatureInvalid(): void {
    $this->signature->method('verify')->willReturn(false);

    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', 'bad');

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesWhenShareMissing(): void {
    $this->signature->method('verify')->willReturn(true);
    $this->shareRepository->method('findByShareable')->willReturn(null);

    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', 'sig');

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesWhenShareUnpublished(): void {
    $this->signature->method('verify')->willReturn(true);

    $share = $this->createStub(ShareView::class);
    $share->method('isPublished')->willReturn(false);
    $this->shareRepository->method('findByShareable')->willReturn($share);

    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', 'sig');

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesWhenImageNotInCollection(): void {
    $this->signature->method('verify')->willReturn(true);

    $share = $this->createStub(ShareView::class);
    $share->method('isPublished')->willReturn(true);
    $this->shareRepository->method('findByShareable')->willReturn($share);
    $this->collectionItemRepository->method('findByCollectionAndItemId')->willReturn(null);

    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', 'sig');

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itGrantsWhenAllChecksPass(): void {
    $this->signature->method('verify')->willReturn(true);

    $share = $this->createStub(ShareView::class);
    $share->method('isPublished')->willReturn(true);
    $this->shareRepository->method('findByShareable')->willReturn($share);

    $item = $this->createStub(CollectionItemView::class);
    $this->collectionItemRepository->method('findByCollectionAndItemId')->willReturn($item);

    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', 'sig');

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itUsesShareableTypeCollectionForLookup(): void {
    $this->signature->method('verify')->willReturn(true);

    $share = $this->createStub(ShareView::class);
    $share->method('isPublished')->willReturn(true);

    $shareRepository = $this->createMock(ShareRepositoryInterface::class);
    $shareRepository
      ->expects($this->once())
      ->method('findByShareable')
      ->with('collection-id', ShareableType::Collection)
      ->willReturn($share);

    $item = $this->createStub(CollectionItemView::class);
    $this->collectionItemRepository->method('findByCollectionAndItemId')->willReturn($item);

    $voter = new CollectionScopedImageVoter(
      $this->signature,
      $shareRepository,
      $this->collectionItemRepository,
    );

    $context = $this->createContext('image-id', 'collection-id', 'sig');

    $voter->vote($this->createToken(), $context, [ImageAccess::View]);
  }
}
