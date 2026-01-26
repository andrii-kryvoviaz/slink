<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ReadModel\View;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Slink\Image\Domain\Enum\License;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Image\Infrastructure\ReadModel\Repository\ImageRepository;
use Slink\Shared\Domain\Contract\CursorAwareInterface;
use Slink\Shared\Infrastructure\Attribute\Sanitize;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;
use Slink\User\Domain\ValueObject\GuestUser;
use Slink\User\Infrastructure\ReadModel\View\UserView;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Table(name: '`image`')]
#[ORM\Entity(repositoryClass: ImageRepository::class)]
#[ORM\Index(name: 'idx_image_user_created_at', columns: ['user_id', 'created_at'])]
class ImageView extends AbstractView implements CursorAwareInterface {
  #[ORM\ManyToMany(targetEntity: TagView::class)]
  #[ORM\JoinTable(name: 'image_to_tag')]
  #[ORM\JoinColumn(name: 'image_id', referencedColumnName: 'uuid')]
  #[ORM\InverseJoinColumn(name: 'tag_id', referencedColumnName: 'uuid')]
  private Collection $tags;

  #[ORM\OneToOne(targetEntity: ImageLicenseView::class, mappedBy: 'image', cascade: ['persist', 'remove'], fetch: 'EAGER')]
  private ?ImageLicenseView $license = null;

  /**
   * @param string $uuid
   * @param ?UserView $user
   * @param ImageAttributes $attributes
   * @param ImageMetadata|null $metadata
   * @param int $bookmarkCount
   */
  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    #[Groups(['public'])]
    #[SerializedName('id')]
    private string          $uuid,

    #[ORM\ManyToOne(targetEntity: UserView::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'uuid')]
    private ?UserView       $user,

    #[ORM\Embedded(class: ImageAttributes::class, columnPrefix: false)]
    #[Groups(['public'])]
    #[Sanitize]
    private ImageAttributes $attributes,

    #[ORM\Embedded(class: ImageMetadata::class, columnPrefix: false)]
    #[Groups(['public'])]
    private ?ImageMetadata  $metadata = null,

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    #[Groups(['public'])]
    private int             $bookmarkCount = 0,
  ) {
    $this->tags = new ArrayCollection();
  }

  /**
   * @return ImageAttributes
   */
  public function getAttributes(): ImageAttributes {
    return $this->attributes;
  }

  /**
   * @return string
   */
  public function getCursorId(): string {
    return $this->uuid;
  }

  /**
   * @return DateTimeInterface
   */
  public function getCursorTimestamp(): DateTimeInterface {
    return $this->attributes->getCreatedAt();
  }

  /**
   * @return string|null
   */
  public function getDescription(): ?string {
    return $this->attributes->getDescription();
  }

  /**
   * @return string
   */
  public function getFileName(): string {
    return $this->attributes->getFileName();
  }

  /**
   * @return ImageMetadata|null
   */
  public function getMetadata(): ?ImageMetadata {
    return $this->metadata;
  }

  /**
   * @return string
   */
  public function getMimeType(): string {
    return $this->metadata?->getMimeType() ?? 'unknown';
  }

  /**
   * @return ?UserView
   */
  public function getUser(): ?UserView {
    return $this->user;
  }

  /**
   * @return string
   */
  public function getUuid(): string {
    return $this->uuid;
  }

  #[Groups(['public'])]
  #[SerializedName('owner')]
  public function getOwner(): UserView|GuestUser {
    return $this->user ?? GuestUser::create();
  }

  /**
   * @param ImageAttributes $attributes
   * @return void
   */
  public function updateAttributes(ImageAttributes $attributes): void {
    $this->attributes = $attributes;
  }

  /**
   * @param ImageMetadata $metadata
   * @return void
   */
  public function updateMetadata(ImageMetadata $metadata): void {
    $this->metadata = $metadata;
  }

  #[Groups(['private'])]
  #[SerializedName('tags')]
  public function getTags(): Collection {
    return $this->tags;
  }

  public function addTag(TagView $tag): void {
    if (!$this->tags->contains($tag)) {
      $this->tags->add($tag);
    }
  }

  public function removeTag(TagView $tag): void {
    if ($this->tags->contains($tag)) {
      $this->tags->removeElement($tag);
    }
  }

  public function getBookmarkCount(): int {
    return $this->bookmarkCount;
  }

  public function incrementBookmarkCount(): void {
    $this->bookmarkCount++;
  }

  public function decrementBookmarkCount(): void {
    if ($this->bookmarkCount > 0) {
      $this->bookmarkCount--;
    }
  }

  public function getLicenseView(): ?ImageLicenseView {
    return $this->license;
  }

  public function getLicense(): ?License {
    return $this->license?->getLicense();
  }

  public function getEffectiveLicense(): License {
    return $this->license?->getLicense() ?? License::AllRightsReserved;
  }

  public function setLicense(?ImageLicenseView $license): void {
    $this->license = $license;
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'id' => $this->uuid,
      'user' => $this->getOwner(),
      'bookmarkCount' => $this->bookmarkCount,
      'license' => $this->getLicense()?->value,
      ...$this->attributes->toPayload(),
      ...$this->metadata ? $this->metadata->toPayload() : []
    ];
  }
}