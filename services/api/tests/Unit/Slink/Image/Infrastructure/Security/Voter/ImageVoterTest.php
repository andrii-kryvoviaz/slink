<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Infrastructure\Security\Voter;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Enum\ImageAccess;
use Slink\Image\Domain\ValueObject\ImageAccessContext;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Image\Infrastructure\Security\Voter\ImageVoter;
use Slink\Share\Application\Service\ShareAccessGuard;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Domain\ValueObject\TargetPath;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Infrastructure\ReadModel\View\UserView;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class ImageVoterTest extends TestCase {
  private ShareRepositoryInterface&Stub $shareRepository;
  private ShareAccessGuard $accessGuard;

  protected function setUp(): void {
    parent::setUp();

    $this->shareRepository = $this->createStub(ShareRepositoryInterface::class);
    $this->accessGuard = $this->createStub(ShareAccessGuard::class);
  }

  private function createVoter(): ImageVoter {
    return new ImageVoter(
      $this->shareRepository,
      $this->accessGuard,
    );
  }

  private function createToken(string $userIdentifier = ''): TokenInterface&Stub {
    $token = $this->createStub(TokenInterface::class);
    $token->method('getUserIdentifier')->willReturn($userIdentifier);

    return $token;
  }

  private function createImageView(
    ?string $ownerId,
    string $imageId = '550e8400-e29b-41d4-a716-446655440000',
    bool $isPublic = false,
    string $fileName = 'test.jpg',
  ): ImageView&Stub {
    $user = null;

    if ($ownerId !== null) {
      $user = $this->createStub(UserView::class);
      $user->method('getUuid')->willReturn($ownerId);
    }

    $attributes = $this->createStub(ImageAttributes::class);
    $attributes->method('isPublic')->willReturn($isPublic);

    $image = $this->createStub(ImageView::class);
    $image->method('getUuid')->willReturn($imageId);
    $image->method('getFileName')->willReturn($fileName);
    $image->method('getUser')->willReturn($user);
    $userId = null;

    if ($ownerId !== null) {
      $userId = ID::fromString($ownerId);
    }

    $image->method('getUserId')->willReturn($userId);
    $image->method('getAttributes')->willReturn($attributes);

    return $image;
  }

  private function createAccessibleShareStub(): ShareView&Stub {
    $this->accessGuard->method('allows')->willReturn(true);

    return $this->createStub(ShareView::class);
  }

  private function createInaccessibleShareStub(): ShareView&Stub {
    $this->accessGuard->method('allows')->willReturn(false);

    return $this->createStub(ShareView::class);
  }

  #[Test]
  public function itAbstainsForUnsupportedAttribute(): void {
    $voter = $this->createVoter();
    $image = $this->createImageView('550e8400-e29b-41d4-a716-446655440000');

    $result = $voter->vote($this->createToken(), $image, ['unknown.attribute']);

    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
  }

  #[Test]
  public function itAbstainsForUnsupportedSubject(): void {
    $voter = $this->createVoter();

    $result = $voter->vote($this->createToken(), new \stdClass(), [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
  }

  #[Test]
  public function itGrantsViewToOwner(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $voter = $this->createVoter();
    $image = $this->createImageView($ownerId);

    $result = $voter->vote($this->createToken($ownerId), $image, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itGrantsViewToNonOwnerWhenImageIsPublic(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $requesterId = '660e8400-e29b-41d4-a716-446655440000';

    $voter = $this->createVoter();
    $image = $this->createImageView($ownerId, isPublic: true);

    $result = $voter->vote($this->createToken($requesterId), $image, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itGrantsViewToAnonymousUserWhenImageIsPublic(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';

    $voter = $this->createVoter();
    $image = $this->createImageView($ownerId, isPublic: true);

    $result = $voter->vote($this->createToken(), $image, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDeniesViewToNonOwnerWhenContextHasNoTargetPath(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $requesterId = '660e8400-e29b-41d4-a716-446655440000';

    $voter = $this->createVoter();
    $image = $this->createImageView($ownerId);
    $context = new ImageAccessContext($image);

    $result = $voter->vote($this->createToken($requesterId), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesViewToNonOwnerWhenSubjectIsBareImageView(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $requesterId = '660e8400-e29b-41d4-a716-446655440000';

    $voter = $this->createVoter();
    $image = $this->createImageView($ownerId);

    $result = $voter->vote($this->createToken($requesterId), $image, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesViewToNonOwnerWhenTargetPathHasNoMatchingShare(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $requesterId = '660e8400-e29b-41d4-a716-446655440000';

    $this->shareRepository->method('findByTargetPath')->willReturn(null);

    $voter = $this->createVoter();
    $image = $this->createImageView($ownerId);
    $context = new ImageAccessContext($image, targetPath: TargetPath::fromString('/image/test.jpg'));

    $result = $voter->vote($this->createToken($requesterId), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itGrantsViewToNonOwnerWhenTargetPathMatchesAccessibleShare(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $requesterId = '660e8400-e29b-41d4-a716-446655440000';

    $this->shareRepository->method('findByTargetPath')->willReturn($this->createAccessibleShareStub());

    $voter = $this->createVoter();
    $image = $this->createImageView($ownerId);
    $context = new ImageAccessContext($image, targetPath: TargetPath::fromString('/image/test.jpg'));

    $result = $voter->vote($this->createToken($requesterId), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDeniesViewToNonOwnerWhenTargetPathMatchesExpiredShare(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $requesterId = '660e8400-e29b-41d4-a716-446655440000';

    $this->shareRepository->method('findByTargetPath')->willReturn($this->createInaccessibleShareStub());

    $voter = $this->createVoter();
    $image = $this->createImageView($ownerId);
    $context = new ImageAccessContext($image, targetPath: TargetPath::fromString('/image/test.jpg'));

    $result = $voter->vote($this->createToken($requesterId), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itGrantsEditToOwner(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $voter = $this->createVoter();
    $image = $this->createImageView($ownerId);

    $result = $voter->vote($this->createToken($ownerId), $image, [ImageAccess::Edit]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDeniesEditToNonOwnerEvenWithAccessibleShare(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $requesterId = '660e8400-e29b-41d4-a716-446655440000';

    $this->shareRepository->method('findByTargetPath')->willReturn($this->createAccessibleShareStub());

    $voter = $this->createVoter();
    $image = $this->createImageView($ownerId);
    $context = new ImageAccessContext($image, targetPath: TargetPath::fromString('/image/test.jpg'));

    $result = $voter->vote($this->createToken($requesterId), $context, [ImageAccess::Edit]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itGrantsDeleteToOwner(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $voter = $this->createVoter();
    $image = $this->createImageView($ownerId);

    $result = $voter->vote($this->createToken($ownerId), $image, [ImageAccess::Delete]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDeniesDeleteToNonOwner(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $requesterId = '660e8400-e29b-41d4-a716-446655440000';

    $voter = $this->createVoter();
    $image = $this->createImageView($ownerId);

    $result = $voter->vote($this->createToken($requesterId), $image, [ImageAccess::Delete]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itAbstainsForTagAttribute(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $voter = $this->createVoter();
    $image = $this->createImageView($ownerId);

    $result = $voter->vote($this->createToken($ownerId), $image, [ImageAccess::Tag]);

    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
  }

  #[Test]
  public function itUnwrapsImageAccessContextForVoting(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $voter = $this->createVoter();
    $image = $this->createImageView($ownerId);
    $context = new ImageAccessContext($image);

    $result = $voter->vote($this->createToken($ownerId), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }
}
