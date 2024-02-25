<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\View;

use Doctrine\ORM\Mapping as ORM;
use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\Shared\Domain\ValueObject\DateTime;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Infrastructure\ReadModel\Repository\UserRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Table(name: '`user`')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
final class UserView extends AbstractView {
  /**
   * @param string $uuid
   * @param Email $email
   * @param DisplayName $displayName
   * @param HashedPassword $password
   * @param DateTime $createdAt
   * @param DateTime|null $updatedAt
   * @param UserStatus $status
   */
  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    #[Groups(['public', 'internal'])]
    #[SerializedName('id')]
    private readonly string $uuid,

    #[ORM\Column(type: 'email', unique: true)]
    #[Groups(['public', 'internal'])]
    private Email $email,
    
    #[ORM\Column(type: 'display_name', unique: true, nullable: true)]
    #[Groups(['public', 'internal'])]
    #[SerializedName('display_name')]
    private DisplayName $displayName,

    #[ORM\Column(type: 'hashed_password')]
    #[Groups(['internal'])]
    private HashedPassword $password,

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['internal'])]
    #[SerializedName('created_at')]
    private readonly DateTime $createdAt,

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['internal'])]
    #[SerializedName('updated_at')]
    private ?DateTime $updatedAt,
    
    #[ORM\Column(enumType: UserStatus::class, options: ['default' => UserStatus::Active])]
    #[Groups(['internal', 'status_check'])]
    private UserStatus $status,
  ) {
  }

  /**
   * @param array<string, mixed> $payload
   * @throws DateTimeException
   */
  public static function deserialize(array $payload): static {
    return new self(
      $payload['id'],
      Email::fromString($payload['credentials']['email']),
      DisplayName::fromString($payload['displayName']),
      HashedPassword::fromHash($payload['credentials']['password']),
      DateTime::fromString($payload['createdAt']),
      isset($payload['updatedAt']) ? DateTime::fromString($payload['updatedAt']) : null,
      $payload['status'],
    );
  }
  
  /**
   * @return string
   */
  public function getUuid(): string {
    return $this->uuid;
  }
  
  /**
   * @return string
   */
  public function getEmail(): string {
    return $this->email->toString();
  }
  
  /**
   * @return string
   */
  public function getDisplayName(): string {
    return $this->displayName->toString();
  }
  
  /**
   * @return HashedPassword
   */
  public function getPassword(): HashedPassword {
    return $this->password;
  }
  
  /**
   * @return DateTime
   */
  public function getCreatedAt(): DateTime {
    return $this->createdAt;
  }
  
  /**
   * @return DateTime|null
   */
  public function getUpdatedAt(): ?DateTime {
    return $this->updatedAt;
  }
  
  /**
   * @return string
   */
  public function getStatus(): string {
    return $this->status->value;
  }
  
  /**
   * @param UserStatus $status
   * @return void
   */
  public function setStatus(UserStatus $status): void {
    $this->status = $status;
  }
}