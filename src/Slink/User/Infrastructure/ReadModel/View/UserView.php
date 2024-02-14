<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\View;

use Doctrine\ORM\Mapping as ORM;
use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\Shared\Domain\ValueObject\DateTime;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Infrastructure\ReadModel\Repository\UserRepository;

#[ORM\Table(name: '`user`')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
final class UserView extends AbstractView {
  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private readonly string $uuid,

    #[ORM\Column(type: 'email', unique: true)]
    private Email $email,

    #[ORM\Column(type: 'hashed_password')]
    private HashedPassword $password,

    #[ORM\Column(type: 'datetime_immutable')]
    private readonly DateTime $createdAt,

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTime $updatedAt,
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
      HashedPassword::fromHash($payload['credentials']['password']),
      DateTime::fromString($payload['createdAt']),
      isset($payload['updatedAt']) ? DateTime::fromString($payload['updatedAt']) : null,
    );
  }

  public function getUuid(): string {
    return $this->uuid;
  }

  public function getEmail(): Email {
    return $this->email;
  }

  public function getPassword(): HashedPassword {
    return $this->password;
  }

  public function getCreatedAt(): DateTime {
    return $this->createdAt;
  }

  public function getUpdatedAt(): ?DateTime {
    return $this->updatedAt;
  }
}