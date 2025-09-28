<?php

declare(strict_types=1);

namespace Slink\Image\Domain\ValueObject;

use Slink\Shared\Domain\DataStructures\HashMap;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class TagSet extends AbstractValueObject {
  private HashMap $tags;
  
  /**
   * @param array<ID> $tags
   */
  private function __construct(array $tags) {
    $this->tags = new HashMap();
    
    foreach($tags as $tag) {
      $this->addTag($tag);
    }
  }
  
  /**
   * @param array<ID> $tags
   * @return self
   */
  public static function create(array $tags = []): self {
    return new self($tags);
  }
  
  /**
   * @return array<ID>
   */
  public function getTags(): array {
    return array_values($this->tags->toArray());
  }
  
  /**
   * @return array<string>
   */
  public function toArray(): array {
    return array_map(
      fn(ID $tag) => $tag->toString(),
      $this->getTags()
    );
  }
  
  /**
   * @param ID $tag
   * @return bool
   */
  public function contains(ID $tag): bool {
    return $this->tags->has($tag->toString());
  }
  
  /**
   * @param ID $tag
   */
  public function addTag(ID $tag): void {
    if($this->contains($tag)) {
      return;
    }
    
    $this->tags->set($tag->toString(), $tag);
  }
  
  /**
   * @param ID $tag
   */
  public function removeTag(ID $tag): void {
    $this->tags->remove($tag->toString());
  }
  
  /**
   * @return int
   */
  public function count(): int {
    return $this->tags->count();
  }
  
  /**
   * @return bool
   */
  public function isEmpty(): bool {
    return $this->tags->count() === 0;
  }
  
  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'tags' => $this->toArray()
    ];
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return self
   */
  public static function fromPayload(array $payload): self {
    $tags = array_map(
      fn(string $tagId) => ID::fromString($tagId),
      $payload['tags'] ?? []
    );
    
    return self::create($tags);
  }
}