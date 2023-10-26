<?php

declare(strict_types=1);

namespace Slik\User\Infrastructure\ReadModel\View;

use Doctrine\ORM\Mapping as ORM;
use Slik\Shared\Domain\Exception\DateTimeException;
use Slik\Shared\Domain\ValueObject\DateTime;
use Slik\Shared\Infrastructure\Persistence\ReadModel\AbstractView;
use Slik\User\Infrastructure\ReadModel\Repository\UserRepository;

#[ORM\Table(name: '`user`')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
final readonly class UserView extends AbstractView {
  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $uuid,

    #[ORM\Column(type: 'email', unique: true)]
    private string $email,

    #[ORM\Column(type: 'hashed_password')]
    private string $password,

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTime $createdAt,

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTime $updatedAt,
  ) {
  }

  /**
   * @throws DateTimeException
   */
  public static function deserialize(array $payload): static {
    return new self(
      $payload['id'],
      $payload['credentials']['email'],
      $payload['credentials']['password'],
      DateTime::fromString($payload['createdAt']),
      isset($payload['updatedAt']) ? DateTime::fromString($payload['updatedAt']) : null,
    );
  }

  public function getUuid(): string {
    return $this->uuid;
  }

  public function getEmail(): string {
    return $this->email;
  }

  public function getPassword(): string {
    return $this->password;
  }

  public function getCreatedAt(): DateTime {
    return $this->createdAt;
  }

  public function getUpdatedAt(): ?DateTime {
    return $this->updatedAt;
  }
}