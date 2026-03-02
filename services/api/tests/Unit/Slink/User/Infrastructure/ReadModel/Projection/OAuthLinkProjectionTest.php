<?php

declare(strict_types=1);

namespace Unit\Slink\User\Infrastructure\ReadModel\Projection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\Event\OAuth\OAuthAccountWasLinked;
use Slink\User\Domain\Event\OAuth\OAuthAccountWasUnlinked;
use Slink\User\Domain\Repository\OAuthLinkRepositoryInterface;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\OAuth\SubjectId;
use Slink\User\Infrastructure\ReadModel\Projection\OAuthLinkProjection;
use Slink\User\Infrastructure\ReadModel\View\OAuthLinkView;

final class OAuthLinkProjectionTest extends TestCase {

  #[Test]
  public function itCreatesViewOnAccountLinked(): void {
    $repository = $this->createMock(OAuthLinkRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('save')
      ->with($this->isInstanceOf(OAuthLinkView::class));

    $projection = new OAuthLinkProjection($repository);

    $event = new OAuthAccountWasLinked(
      userId: ID::generate(),
      linkId: ID::generate(),
      provider: OAuthProvider::Google,
      sub: SubjectId::fromString('google-sub-123'),
      email: Email::fromString('user@example.com'),
      linkedAt: DateTime::now(),
    );

    $projection->handleOAuthAccountWasLinked($event);
  }

  #[Test]
  public function itDeletesViewOnAccountUnlinked(): void {
    $linkId = ID::generate();
    $linkView = $this->createStub(OAuthLinkView::class);

    $repository = $this->createMock(OAuthLinkRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('findById')
      ->with($linkId->toString())
      ->willReturn($linkView);
    $repository->expects($this->once())
      ->method('delete')
      ->with($linkView);

    $projection = new OAuthLinkProjection($repository);

    $event = new OAuthAccountWasUnlinked(
      userId: ID::generate(),
      linkId: $linkId,
      provider: OAuthProvider::Google,
      sub: SubjectId::fromString('google-sub-123'),
    );

    $projection->handleOAuthAccountWasUnlinked($event);
  }

  #[Test]
  public function itHandlesUnlinkOfNonexistentLink(): void {
    $linkId = ID::generate();

    $repository = $this->createMock(OAuthLinkRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('findById')
      ->with($linkId->toString())
      ->willReturn(null);
    $repository->expects($this->never())
      ->method('delete');

    $projection = new OAuthLinkProjection($repository);

    $event = new OAuthAccountWasUnlinked(
      userId: ID::generate(),
      linkId: $linkId,
      provider: OAuthProvider::Google,
      sub: SubjectId::fromString('google-sub-123'),
    );

    $projection->handleOAuthAccountWasUnlinked($event);
  }
}
