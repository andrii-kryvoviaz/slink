<?php

declare(strict_types=1);

namespace Slink\Tag\Domain;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Event\TagWasCreated;
use Slink\Tag\Domain\Event\TagWasDeleted;
use Slink\Tag\Domain\ValueObject\TagName;
use Slink\Tag\Domain\ValueObject\TagPath;

final class Tag extends AbstractAggregateRoot {
  private ID $userId;
  private TagName $name;
  private ?TagPath $path = null;
  private ?ID $parentId;
  private bool $deleted = false;

  public static function create(
    ID       $id,
    ID       $userId,
    TagName  $name,
    ?ID      $parentId = null,
    ?TagPath $parentPath = null
  ): self {
    $path = $parentPath
      ? TagPath::createChild($parentPath, $name)
      : TagPath::createRoot($name);

    $tag = new self($id);
    $tag->recordThat(new TagWasCreated($id, $userId, $name, $path, $parentId));

    return $tag;
  }

  public function delete(): void {
    if ($this->deleted) {
      return;
    }

    $this->recordThat(new TagWasDeleted($this->aggregateRootId()));
  }

  public function applyTagWasCreated(TagWasCreated $event): void {
    $this->userId = $event->userId;
    $this->name = $event->name;
    $this->path = $event->path;
    $this->parentId = $event->parentId;
  }

  public function applyTagWasDeleted(TagWasDeleted $event): void {
    $this->deleted = true;
  }

  public function getUserId(): ID {
    return $this->userId;
  }

  public function getName(): TagName {
    return $this->name;
  }

  public function getPath(): TagPath {
    return $this->path ?? TagPath::fromString('#');
  }

  public function getParentId(): ?ID {
    return $this->parentId;
  }

  public function isDeleted(): bool {
    return $this->deleted;
  }

  public function hasParent(): bool {
    return $this->parentId !== null;
  }

  public function isRoot(): bool {
    return $this->parentId === null;
  }

  protected function createSnapshotState(): array {
    return [
      'userId' => $this->userId->toString(),
      'name' => $this->name->toPayload(),
      'path' => ($this->path?->toPayload()) ?? ['value' => '#'],
      'parentId' => $this->parentId?->toString(),
      'deleted' => $this->deleted,
    ];
  }

  protected static function reconstituteFromSnapshotState(AggregateRootId $id, $state): AggregateRootWithSnapshotting {
    $tag = new static($id);

    $tag->userId = ID::fromString($state['userId']);
    $tag->name = TagName::fromPayload($state['name']);
    $tag->path = TagPath::fromPayload($state['path']);
    $tag->parentId = $state['parentId'] ? ID::fromString($state['parentId']) : null;
    $tag->deleted = $state['deleted'];

    return $tag;
  }
}