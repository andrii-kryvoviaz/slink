<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\Event\OAuth;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Event\OAuth\OAuthAccountWasUnlinked;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;
use Slink\User\Domain\ValueObject\OAuth\SubjectId;

final class OAuthAccountWasUnlinkedTest extends TestCase {
  #[Test]
  public function itRoundtripsThroughPayload(): void {
    $userId = ID::generate();
    $linkId = ID::generate();

    $event = new OAuthAccountWasUnlinked(
      $userId,
      $linkId,
      ProviderSlug::fromString('keycloak'),
      SubjectId::fromString('kc-sub-001'),
    );

    $restored = OAuthAccountWasUnlinked::fromPayload($event->toPayload());

    $this->assertTrue($event->userId->equals($restored->userId));
    $this->assertTrue($event->linkId->equals($restored->linkId));
    $this->assertEquals($event->provider, $restored->provider);
    $this->assertSame($event->sub->toString(), $restored->sub->toString());
  }
}
