<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Comment\Infrastructure\ReadModel\View;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class CommentViewTest extends TestCase {
  private function createUser(UserStatus $status): UserView {
    return new UserView(
      ID::generate()->toString(),
      Email::fromString('author@example.com'),
      Username::fromString('author'),
      DisplayName::fromString('Author'),
      HashedPassword::encode('password123'),
      DateTime::now(),
      null,
      $status,
      null,
    );
  }

  private function createComment(UserView $user): CommentView {
    return new CommentView(
      ID::generate()->toString(),
      $this->createStub(ImageView::class),
      $user,
      'Test comment',
      DateTime::now(),
    );
  }

  #[Test]
  public function itReturnsAuthorForActiveUser(): void {
    $user = $this->createUser(UserStatus::Active);
    $comment = $this->createComment($user);

    $this->assertSame($user, $comment->getUser());
    $this->assertSame($user->getUuid(), $comment->getUserId());
  }

  #[Test]
  public function itReturnsNullAuthorForDeletedUser(): void {
    $user = $this->createUser(UserStatus::Deleted);
    $comment = $this->createComment($user);

    $this->assertNull($comment->getUser());
    $this->assertNull($comment->getUserId());
  }
}
