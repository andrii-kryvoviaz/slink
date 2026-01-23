<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\ReadModel\View;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Slink\Collection\Infrastructure\ReadModel\Repository\CollectionRepository;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;
use Slink\User\Infrastructure\ReadModel\View\UserView;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Table(name: '`collection`')]
#[ORM\Entity(repositoryClass: CollectionRepository::class)]
#[ORM\Index(columns: ['user_id', 'created_at'], name: 'idx_collection_user_created_at')]
class CollectionView extends AbstractView {
  #[ORM\OneToMany(targetEntity: CollectionItemView::class, mappedBy: 'collection', cascade: ['remove'], orphanRemoval: true)]
  private Collection $items;

  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    #[Groups(['public'])]
    #[SerializedName('id')]
    private string $uuid,

    #[ORM\ManyToOne(targetEntity: UserView::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'uuid', nullable: false)]
    private UserView $user,

    #[ORM\Column(type: 'string', length: 100)]
    #[Groups(['public'])]
    private string $name,

    #[ORM\Column(type: 'string', length: 500)]
    #[Groups(['public'])]
    private string $description,

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['public'])]
    private DateTime $createdAt,

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['public'])]
    private ?DateTime $updatedAt = null,
  ) {
    $this->items = new ArrayCollection();
  }

  public function getId(): string {
    return $this->uuid;
  }

  public function getUuid(): string {
    return $this->uuid;
  }

  public function getUser(): UserView {
    return $this->user;
  }

  public function getUserId(): string {
    return $this->user->getUuid();
  }

  public function getItems(): Collection {
    return $this->items;
  }

  public function getName(): string {
    return $this->name;
  }

  public function getDescription(): string {
    return $this->description;
  }

  public function getCreatedAt(): DateTime {
    return $this->createdAt;
  }

  public function getUpdatedAt(): ?DateTime {
    return $this->updatedAt;
  }

  public function updateDetails(string $name, string $description, DateTime $updatedAt): void {
    $this->name = $name;
    $this->description = $description;
    $this->updatedAt = $updatedAt;
  }
}
