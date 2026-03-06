<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\ValueObject\OAuth;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\ValueObject\OAuth\OAuthSubject;
use Slink\User\Domain\ValueObject\OAuth\OAuthSubjectSet;

final class OAuthSubjectSetTest extends TestCase {

  #[Test]
  public function itStartsEmpty(): void {
    $set = OAuthSubjectSet::create();

    $this->assertSame([], $set->toArray());
  }

  #[Test]
  public function itAddsAndHasSubject(): void {
    $subject = OAuthSubject::fromPrimitives('google', 'sub-1');
    $set = OAuthSubjectSet::create();

    $set->add($subject);

    $this->assertTrue($set->has($subject));
  }

  #[Test]
  public function itRemovesSubject(): void {
    $subject = OAuthSubject::fromPrimitives('authentik', 'sub-2');
    $set = OAuthSubjectSet::create([$subject]);

    $this->assertTrue($set->has($subject));

    $set->remove($subject);

    $this->assertFalse($set->has($subject));
  }

  #[Test]
  public function itHandlesIdempotentAdd(): void {
    $subject = OAuthSubject::fromPrimitives('keycloak', 'sub-3');
    $set = OAuthSubjectSet::create();

    $set->add($subject);
    $set->add($subject);

    $this->assertCount(1, $set->toArray());
  }

  #[Test]
  public function itRoundtripsViaToArrayFromArray(): void {
    $subjectA = OAuthSubject::fromPrimitives('google', 'id-a');
    $subjectB = OAuthSubject::fromPrimitives('authentik', 'id-b');
    $original = OAuthSubjectSet::create([$subjectA, $subjectB]);

    $restored = OAuthSubjectSet::fromArray($original->toArray());

    $this->assertTrue($restored->has($subjectA));
    $this->assertTrue($restored->has($subjectB));
    $this->assertSame($original->toArray(), $restored->toArray());
  }
}
