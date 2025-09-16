<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\EventStore\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(
  name: 'snapshots',
  uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'unique_aggregate_snapshot', columns: ['aggregate_root_id'])
  ],
  indexes: [
    new ORM\Index(name: 'idx_created_at', columns: ['created_at'])
  ]
)]
class SnapshotEntity {
  
  #[ORM\Id]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  #[ORM\Column(type: 'integer')]
  private int $id; // @phpstan-ignore-line property.onlyRead
  
  #[ORM\Column(name: 'aggregate_root_id', type: 'string', length: 36)]
  private string $aggregateRootId;
  
  #[ORM\Column(name: 'aggregate_version', type: 'integer')]
  private int $aggregateVersion;
  
  /**
   * @var array<string, mixed>
   */
  #[ORM\Column(type: 'json')]
  private array $state;
  
  #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
  private \DateTimeImmutable $createdAt;
  
  /**
   * @param array<string, mixed> $state
   */
  public function __construct(
    string $aggregateRootId,
    int $aggregateVersion,
    array $state,
    \DateTimeImmutable $createdAt
  ) {
    $this->aggregateRootId = $aggregateRootId;
    $this->aggregateVersion = $aggregateVersion;
    $this->state = $state;
    $this->createdAt = $createdAt;
  }
  
  public function getId(): int {
    return $this->id;
  }
  
  public function getAggregateRootId(): string {
    return $this->aggregateRootId;
  }
  
  public function getAggregateVersion(): int {
    return $this->aggregateVersion;
  }
  
  /**
   * @return array<string, mixed>
   */
  public function getState(): array {
    return $this->state;
  }
  
  public function getCreatedAt(): \DateTimeImmutable {
    return $this->createdAt;
  }
  
  /**
   * @param array<string, mixed> $state
   */
  public function updateState(array $state, int $version): void {
    $this->state = $state;
    $this->aggregateVersion = $version;
    $this->createdAt = new \DateTimeImmutable();
  }
}