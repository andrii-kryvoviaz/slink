<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Infrastructure\Security\Voter;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Enum\TagAccess;
use Slink\Tag\Domain\Tag;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;
use Slink\Tag\Infrastructure\Security\Voter\TagVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class TagVoterTest extends TestCase {
  private const OWNER_ID = '550e8400-e29b-41d4-a716-446655440000';
  private const OTHER_ID = '660e8400-e29b-41d4-a716-446655440000';

  private function createVoter(): TagVoter {
    return new TagVoter();
  }

  private function createToken(string $userIdentifier = ''): TokenInterface&Stub {
    $token = $this->createStub(TokenInterface::class);
    $token->method('getUserIdentifier')->willReturn($userIdentifier);

    return $token;
  }

  private function createTagView(string $ownerId): TagView&Stub {
    $tag = $this->createStub(TagView::class);
    $tag->method('getUserId')->willReturn($ownerId);

    return $tag;
  }

  private function createTagAggregate(string $ownerId): Tag {
    $tag = $this->createStub(Tag::class);
    $tag->method('getUserId')->willReturn(ID::fromString($ownerId));

    return $tag;
  }

  #[Test]
  public function itAbstainsForUnsupportedAttribute(): void {
    $voter = $this->createVoter();
    $tag = $this->createTagView(self::OWNER_ID);

    $result = $voter->vote($this->createToken(self::OWNER_ID), $tag, ['unknown.attribute']);

    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
  }

  #[Test]
  public function itAbstainsForUnsupportedSubject(): void {
    $voter = $this->createVoter();

    $result = $voter->vote($this->createToken(self::OWNER_ID), new \stdClass(), [TagAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
  }

  #[Test]
  public function itGrantsViewToOwner(): void {
    $voter = $this->createVoter();
    $tag = $this->createTagView(self::OWNER_ID);

    $result = $voter->vote($this->createToken(self::OWNER_ID), $tag, [TagAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDeniesViewToNonOwner(): void {
    $voter = $this->createVoter();
    $tag = $this->createTagView(self::OWNER_ID);

    $result = $voter->vote($this->createToken(self::OTHER_ID), $tag, [TagAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itGrantsEditToOwner(): void {
    $voter = $this->createVoter();
    $tag = $this->createTagView(self::OWNER_ID);

    $result = $voter->vote($this->createToken(self::OWNER_ID), $tag, [TagAccess::Edit]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDeniesEditToNonOwner(): void {
    $voter = $this->createVoter();
    $tag = $this->createTagView(self::OWNER_ID);

    $result = $voter->vote($this->createToken(self::OTHER_ID), $tag, [TagAccess::Edit]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itGrantsDeleteToOwner(): void {
    $voter = $this->createVoter();
    $tag = $this->createTagView(self::OWNER_ID);

    $result = $voter->vote($this->createToken(self::OWNER_ID), $tag, [TagAccess::Delete]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDeniesDeleteToNonOwner(): void {
    $voter = $this->createVoter();
    $tag = $this->createTagView(self::OWNER_ID);

    $result = $voter->vote($this->createToken(self::OTHER_ID), $tag, [TagAccess::Delete]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itGrantsUseToOwner(): void {
    $voter = $this->createVoter();
    $tag = $this->createTagView(self::OWNER_ID);

    $result = $voter->vote($this->createToken(self::OWNER_ID), $tag, [TagAccess::Use]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDeniesUseToNonOwner(): void {
    $voter = $this->createVoter();
    $tag = $this->createTagView(self::OWNER_ID);

    $result = $voter->vote($this->createToken(self::OTHER_ID), $tag, [TagAccess::Use]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itGrantsOnTagAggregateSubject(): void {
    $voter = $this->createVoter();
    $tag = $this->createTagAggregate(self::OWNER_ID);

    $result = $voter->vote($this->createToken(self::OWNER_ID), $tag, [TagAccess::Edit]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }
}
