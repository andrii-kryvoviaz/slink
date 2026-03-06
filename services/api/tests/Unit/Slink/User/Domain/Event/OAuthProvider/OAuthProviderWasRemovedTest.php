<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\Event\OAuthProvider;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasRemoved;

final class OAuthProviderWasRemovedTest extends TestCase {
  #[Test]
  public function itRoundtripsThroughPayload(): void {
    $event = new OAuthProviderWasRemoved('provider-id-1');

    $restored = OAuthProviderWasRemoved::fromPayload($event->toPayload());

    $this->assertSame($event->id, $restored->id);
  }
}
