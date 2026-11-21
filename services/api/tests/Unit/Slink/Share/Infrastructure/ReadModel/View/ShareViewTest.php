<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Infrastructure\ReadModel\View;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\ValueObject\AccessControl;
use Slink\Share\Domain\ValueObject\HashedSharePassword;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Shared\Domain\ValueObject\Date\DateTime;

final class ShareViewTest extends TestCase {
  private const string SHARE_ID = '550e8400-e29b-41d4-a716-446655440000';
  private const string SHAREABLE_ID = '660e8400-e29b-41d4-a716-446655440000';
  private const string TARGET_URL = '/image/test.jpg';

  private function createShareView(
    bool $isPublished,
    ?DateTime $expiresAt = null,
  ): ShareView {
    $accessControl = AccessControl::initial($isPublished);

    if ($expiresAt !== null) {
      $accessControl = $accessControl->expireAt($expiresAt);
    }

    return new ShareView(
      self::SHARE_ID,
      new ShareableReference(self::SHAREABLE_ID, ShareableType::Image),
      self::TARGET_URL,
      DateTime::fromString('2024-01-01T00:00:00+00:00'),
      $accessControl,
    );
  }

  #[Test]
  public function itExposesPublicationState(): void {
    $published = $this->createShareView(isPublished: true);
    $unpublished = $this->createShareView(isPublished: false);

    $this->assertTrue($published->isPublished());
    $this->assertFalse($unpublished->isPublished());
  }

  #[Test]
  public function itExposesExpirationState(): void {
    $future = DateTime::fromString('2099-12-31T23:59:59+00:00');

    $active = $this->createShareView(isPublished: true, expiresAt: $future);
    $unbounded = $this->createShareView(isPublished: true);

    $this->assertEquals($future->toString(), $active->getExpiresAt()?->toString());
    $this->assertNull($unbounded->getExpiresAt());
  }

  #[Test]
  #[Group('sc-1177')]
  public function itExposesTheWholeAccessControl(): void {
    $expiresAt = DateTime::fromString('2099-12-31T23:59:59+00:00');
    $accessControl = AccessControl::initial(true)
      ->expireAt($expiresAt)
      ->withPassword(HashedSharePassword::fromHash('stored-hash'));

    $view = new ShareView(
      self::SHARE_ID,
      new ShareableReference(self::SHAREABLE_ID, ShareableType::Image),
      self::TARGET_URL,
      DateTime::fromString('2024-01-01T00:00:00+00:00'),
      $accessControl,
    );

    $this->assertSame($accessControl, $view->getAccessControl());
    $this->assertTrue($view->getAccessControl()->isPublished);
    $this->assertSame($expiresAt->toString(), $view->getAccessControl()->expiresAt?->toString());
    $this->assertSame('stored-hash', $view->getAccessControl()->passwordHash);
  }

  #[Test]
  #[Group('sc-1177')]
  public function itReflectsWritesInTheWholeAccessControl(): void {
    $view = $this->createShareView(isPublished: false);

    $view->markPublished();
    $view->setPassword(HashedSharePassword::fromHash('stored-hash'));

    $this->assertTrue($view->getAccessControl()->isPublished);
    $this->assertSame('stored-hash', $view->getAccessControl()->passwordHash);
  }
}
