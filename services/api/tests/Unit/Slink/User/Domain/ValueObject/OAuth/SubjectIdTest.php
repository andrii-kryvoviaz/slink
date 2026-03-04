<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\ValueObject\OAuth;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\Exception\InvalidValueObjectException;
use Slink\User\Domain\ValueObject\OAuth\SubjectId;

final class SubjectIdTest extends TestCase {

  #[Test]
  public function itCreatesFromValidString(): void {
    $subjectId = SubjectId::fromString('abc-123');

    $this->assertInstanceOf(SubjectId::class, $subjectId);
    $this->assertSame('abc-123', $subjectId->toString());
  }

  #[Test]
  public function itThrowsOnEmptyString(): void {
    $this->expectException(InvalidValueObjectException::class);
    $this->expectExceptionMessage('Invalid SubjectId: cannot be empty');

    SubjectId::fromString('');
  }
}
