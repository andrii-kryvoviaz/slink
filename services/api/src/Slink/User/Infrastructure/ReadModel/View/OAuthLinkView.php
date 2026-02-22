<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\View;

use Doctrine\ORM\Mapping as ORM;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;
use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\OAuth\SubjectId;
use Slink\User\Infrastructure\ReadModel\Repository\OAuthLinkRepository;

#[ORM\Table(name: '`oauth_link`')]
#[ORM\Entity(repositoryClass: OAuthLinkRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_oauth_link_provider', columns: ['provider_slug', 'provider_user_id'])]
#[ORM\Index(columns: ['user_id'], name: 'idx_oauth_link_user')]
class OAuthLinkView extends AbstractView {
  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id,

    #[ORM\Column(type: 'string', length: 36)]
    private string $userId,

    #[ORM\Column(type: 'string', length: 50)]
    private string $providerSlug,

    #[ORM\Column(type: 'string', length: 255)]
    private string $providerUserId,

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $providerEmail,

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTime $linkedAt,
  ) {}

  public static function create(
    ID $id,
    ID $userId,
    OAuthProvider $provider,
    SubjectId $sub,
    ?Email $email,
    DateTime $linkedAt,
  ): self {
    return new self(
      $id->toString(),
      $userId->toString(),
      $provider->value,
      $sub->toString(),
      $email?->toString(),
      $linkedAt,
    );
  }

  public function getId(): string {
    return $this->id;
  }

  public function getUserId(): string {
    return $this->userId;
  }

  public function getProviderSlug(): string {
    return $this->providerSlug;
  }

  public function getProviderUserId(): string {
    return $this->providerUserId;
  }

  public function getProviderEmail(): ?string {
    return $this->providerEmail;
  }

  public function getLinkedAt(): DateTime {
    return $this->linkedAt;
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'id' => $this->id,
      'userId' => $this->userId,
      'providerSlug' => $this->providerSlug,
      'providerUserId' => $this->providerUserId,
      'providerEmail' => $this->providerEmail,
      'linkedAt' => $this->linkedAt->toString(),
    ];
  }
}
