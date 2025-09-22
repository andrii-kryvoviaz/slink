<?php

declare(strict_types=1);

namespace Slink\Tag\Infrastructure\ReadModel\View;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;
use Slink\Tag\Infrastructure\ReadModel\Repository\TagRepository;
use Slink\User\Infrastructure\ReadModel\View\UserView;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Table(name: '`tag`')]
#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Index(columns: ['user_id', 'name'], name: 'idx_tag_user_name')]
#[ORM\Index(columns: ['user_id', 'parent_id'], name: 'idx_tag_user_parent')]
#[ORM\Index(columns: ['user_id', 'path'], name: 'idx_tag_user_path')]
class TagView extends AbstractView {
  #[ORM\ManyToOne(targetEntity: UserView::class)]
  #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'uuid')]
  private ?UserView $user = null;

  #[ORM\ManyToOne(targetEntity: TagView::class)]
  #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'uuid')]
  private ?TagView $parent = null;

  #[ORM\OneToMany(mappedBy: 'parent', targetEntity: TagView::class)]
  private Collection $children;

  #[ORM\ManyToMany(targetEntity: ImageView::class, mappedBy: 'tags')]
  private Collection $images;

  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    #[Groups(['public'])]
    #[SerializedName('id')]
    private readonly string $uuid,

    #[ORM\Column(type: 'string', length: 50)]
    #[Groups(['public'])]
    private string          $name,

    #[ORM\Column(type: 'text')]
    #[Groups(['public'])]
    private string          $path,

    #[ORM\Column(type: 'string', length: 36)]
    #[Groups(['admin'])]
    private string          $userId,

    #[ORM\Column(type: 'string', length: 36, nullable: true)]
    #[Groups(['admin'])]
    private ?string         $parentId = null,
  ) {
    $this->children = new ArrayCollection();
    $this->images = new ArrayCollection();
  }

  public function getUuid(): string {
    return $this->uuid;
  }

  public function getName(): string {
    return $this->name;
  }

  public function getPath(): string {
    return $this->path;
  }

  public function getParentId(): ?string {
    return $this->parentId;
  }

  public function getUserId(): string {
    return $this->userId;
  }

  public function getUser(): ?UserView {
    return $this->user;
  }

  public function setUser(?UserView $user): void {
    $this->user = $user;
  }

  public function getParent(): ?TagView {
    return $this->parent;
  }

  public function setParent(?TagView $parent): void {
    $this->parent = $parent;
  }

  #[Groups(['public'])]
  #[SerializedName('children')]
  public function getChildren(): Collection {
    return $this->children;
  }

  public function addChild(TagView $child): void {
    if (!$this->children->contains($child)) {
      $this->children->add($child);
      $child->setParent($this);
    }
  }

  public function removeChild(TagView $child): void {
    if ($this->children->contains($child)) {
      $this->children->removeElement($child);
      $child->setParent(null);
    }
  }

  #[Groups(['public'])]
  #[SerializedName('images')]
  public function getImages(): Collection {
    return $this->images;
  }

  public function addImage(ImageView $image): void {
    if (!$this->images->contains($image)) {
      $this->images->add($image);
    }
  }

  public function removeImage(ImageView $image): void {
    if ($this->images->contains($image)) {
      $this->images->removeElement($image);
    }
  }

  #[Groups(['public'])]
  #[SerializedName('isRoot')]
  public function isRoot(): bool {
    return $this->parentId === null;
  }

  #[Groups(['public'])]
  #[SerializedName('depth')]
  public function getDepth(): int {
    if (empty($this->path) || $this->path === '#') {
      return 1;
    }

    return substr_count($this->path, '/') + 1;
  }

  #[Groups(['public'])]
  #[SerializedName('imageCount')]
  public function getImageCount(): int {
    return $this->images->count();
  }

  public function toPayload(): array {
    return [
      'id' => $this->uuid,
      'name' => $this->name,
      'path' => $this->path,
      'parentId' => $this->parentId,
      'isRoot' => $this->isRoot(),
      'depth' => $this->getDepth(),
      'imageCount' => $this->getImageCount(),
    ];
  }
}