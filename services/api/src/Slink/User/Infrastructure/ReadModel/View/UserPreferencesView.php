<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\View;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;
use Slink\User\Domain\ValueObject\UserPreferences;
use Slink\User\Infrastructure\ReadModel\Repository\UserPreferencesRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Table(name: 'user_preferences')]
#[ORM\Entity(repositoryClass: UserPreferencesRepository::class)]
class UserPreferencesView extends AbstractView {
  /**
   * @param array<string, mixed> $preferences
   */
  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $id,

    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['internal'])]
    #[SerializedName('userId')]
    private string $userId,

    #[ORM\Column(type: 'json')]
    #[Groups(['internal'])]
    private array $preferences = [],

    #[ORM\Column(type: 'datetime_immutable')]
    private readonly DateTime $createdAt = new DateTime(),

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTime $updatedAt = new DateTime(),
  ) {}

  public static function create(string $userId, UserPreferences $preferences): self {
    return new self(
      Uuid::uuid4()->toString(),
      $userId,
      $preferences->toPayload(),
      DateTime::now(),
      DateTime::now(),
    );
  }

  public function getId(): string {
    return $this->id;
  }

  public function getUserId(): string {
    return $this->userId;
  }

  public function getPreferences(): UserPreferences {
    return UserPreferences::fromPayload($this->preferences);
  }

  public function setPreferences(UserPreferences $preferences): void {
    $this->preferences = $preferences->toPayload();
    $this->updatedAt = DateTime::now();
  }

  public function getCreatedAt(): DateTime {
    return $this->createdAt;
  }

  public function getUpdatedAt(): DateTime {
    return $this->updatedAt;
  }
}
