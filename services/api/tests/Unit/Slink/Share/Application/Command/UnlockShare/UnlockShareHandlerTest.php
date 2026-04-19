<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Application\Command\UnlockShare;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Share\Application\Command\UnlockShare\UnlockShareCommand;
use Slink\Share\Application\Command\UnlockShare\UnlockShareHandler;
use Slink\Share\Domain\Exception\InvalidSharePasswordException;
use Slink\Share\Domain\Repository\ShareStoreRepositoryInterface;
use Slink\Share\Domain\Share;
use Slink\Share\Domain\ValueObject\HashedSharePassword;
use Slink\Share\Infrastructure\Security\ShareUnlockCookieSigner;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

final class UnlockShareHandlerTest extends TestCase {
  private ShareStoreRepositoryInterface&Stub $shareStore;
  private ShareUnlockCookieSigner $signer;
  private UnlockShareHandler $handler;

  protected function setUp(): void {
    parent::setUp();

    $this->shareStore = $this->createStub(ShareStoreRepositoryInterface::class);
    $this->signer = new ShareUnlockCookieSigner('test-secret', 'test', new RequestStack());
    $this->handler = new UnlockShareHandler($this->shareStore, $this->signer);
  }

  #[Test]
  public function itReturnsSignedCookieOnPasswordMatch(): void {
    $shareId = ID::generate();
    $password = HashedSharePassword::encode('hunter2');

    $share = $this->createStub(Share::class);
    $share->method('aggregateRootVersion')->willReturn(1);
    $share->method('getPassword')->willReturn($password);

    $this->shareStore->method('get')->willReturn($share);

    $cookie = $this->handler->__invoke(new UnlockShareCommand('hunter2'), $shareId->toString());

    $this->assertNotNull($cookie);
    $this->assertSame(
      ShareUnlockCookieSigner::cookieName($shareId),
      $cookie->getName(),
    );
  }

  #[Test]
  public function itThrowsWhenPasswordDoesNotMatch(): void {
    $shareId = ID::generate()->toString();
    $password = HashedSharePassword::encode('hunter2');

    $share = $this->createStub(Share::class);
    $share->method('aggregateRootVersion')->willReturn(1);
    $share->method('getPassword')->willReturn($password);

    $this->shareStore->method('get')->willReturn($share);

    $this->expectException(InvalidSharePasswordException::class);

    $this->handler->__invoke(new UnlockShareCommand('wrong'), $shareId);
  }

  #[Test]
  public function itReturnsNullWhenShareHasNoPassword(): void {
    $shareId = ID::generate();

    $share = $this->createStub(Share::class);
    $share->method('aggregateRootVersion')->willReturn(1);
    $share->method('getPassword')->willReturn(null);

    $this->shareStore->method('get')->willReturn($share);

    $cookie = $this->handler->__invoke(new UnlockShareCommand('anything'), $shareId->toString());

    $this->assertNull($cookie);
  }

  #[Test]
  public function itThrowsNotFoundWhenAggregateMissing(): void {
    $shareId = ID::generate()->toString();

    $share = $this->createStub(Share::class);
    $share->method('aggregateRootVersion')->willReturn(0);

    $this->shareStore->method('get')->willReturn($share);

    $this->expectException(NotFoundException::class);

    $this->handler->__invoke(new UnlockShareCommand('hunter2'), $shareId);
  }
}
