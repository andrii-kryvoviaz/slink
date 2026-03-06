<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\DataStructures\HashMap;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class OAuthSubjectSet extends AbstractValueObject {
  private HashMap $subjects;

  /** @param array<OAuthSubject> $subjects */
  private function __construct(array $subjects) {
    $this->subjects = new HashMap();
    foreach ($subjects as $subject) {
      $this->add($subject);
    }
  }

  /** @param array<OAuthSubject> $subjects */
  public static function create(array $subjects = []): self {
    return new self($subjects);
  }

  public function has(OAuthSubject $subject): bool {
    return $this->subjects->has($subject->toKey());
  }

  public function add(OAuthSubject $subject): void {
    if ($this->has($subject)) {
      return;
    }
    $this->subjects->set($subject->toKey(), $subject);
  }

  public function remove(OAuthSubject $subject): void {
    $this->subjects->remove($subject->toKey());
  }

  /** @return array<string> */
  public function toArray(): array {
    return $this->subjects->keys();
  }

  /** @param array<string> $keys */
  public static function fromArray(array $keys): self {
    $subjects = array_map(
      fn(string $key) => OAuthSubject::fromKey($key),
      $keys
    );
    return self::create($subjects);
  }
}
