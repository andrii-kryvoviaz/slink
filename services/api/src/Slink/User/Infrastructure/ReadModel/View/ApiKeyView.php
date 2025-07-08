<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\View;

use Doctrine\ORM\Mapping as ORM;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;
use Slink\User\Infrastructure\ReadModel\Repository\ApiKeyRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Table(name: '`api_key`')]
#[ORM\Entity(repositoryClass: ApiKeyRepository::class)]
#[ORM\Index(columns: ['key_hash'], name: 'idx_api_key_hash')]
#[ORM\Index(columns: ['user_id'], name: 'idx_api_key_user')]
#[ORM\Index(columns: ['created_at'], name: 'idx_api_key_created_at')]
class ApiKeyView extends AbstractView {
  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[Groups(['public'])]
    #[SerializedName('id')]
    private string $keyId,

    #[ORM\Column(type: 'uuid')]
    #[Groups(['internal'])]
    private string $userId,

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['internal'])]
    private string $keyHash,

    #[ORM\Column(type: 'string', length: 100)]
    #[Groups(['public'])]
    private string $name,

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['public'])]
    private DateTime $createdAt,

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['public'])]
    private ?DateTime $expiresAt = null,

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['internal'])]
    private ?DateTime $lastUsedAt = null
  ) {}

  public static function create(
    string $keyId,
    string $userId,
    string $key,
    string $name,
    DateTime $createdAt,
    ?DateTime $expiresAt = null
  ): self {
    return new self(
      $keyId,
      $userId,
      self::hashKey($key),
      $name,
      $createdAt,
      $expiresAt
    );
  }

  private static function hashKey(string $key): string {
    return hash('sha256', $key);
  }

  public function getKeyId(): string {
    return $this->keyId;
  }

  public function getUserId(): string {
    return $this->userId;
  }

  public function getName(): string {
    return $this->name;
  }

  public function getCreatedAt(): DateTime {
    return $this->createdAt;
  }

  public function getExpiresAt(): ?DateTime {
    return $this->expiresAt;
  }

  public function getLastUsedAt(): ?DateTime {
    return $this->lastUsedAt;
  }

  public function updateLastUsed(): void {
    $this->lastUsedAt = DateTime::now();
  }

  public function isExpired(): bool {
    if ($this->expiresAt === null) {
      return false;
    }

    return $this->expiresAt->isBefore(DateTime::now());
  }

  public function verifyKey(string $key): bool {
    return hash_equals($this->keyHash, self::hashKey($key));
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'id' => $this->keyId,
      'name' => $this->name,
      'createdAt' => $this->createdAt->toString(),
      'expiresAt' => $this->expiresAt?->toString(),
      'lastUsedAt' => $this->lastUsedAt?->toString(),
      'isExpired' => $this->isExpired()
    ];
  }
}
