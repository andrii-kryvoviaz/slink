<?php

declare(strict_types=1);

namespace Slink\Bookmark\Infrastructure\ReadModel\View;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Slink\Bookmark\Infrastructure\ReadModel\Repository\BookmarkRepository;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\Contract\CursorAwareInterface;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;
use Slink\User\Domain\ValueObject\GuestUser;
use Slink\User\Infrastructure\ReadModel\View\UserView;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Table(name: '`bookmark`')]
#[ORM\Entity(repositoryClass: BookmarkRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_user_image_bookmark', columns: ['user_id', 'image_id'])]
#[ORM\Index(columns: ['user_id', 'created_at'], name: 'idx_bookmark_user_created')]
#[ORM\Index(columns: ['image_id'], name: 'idx_bookmark_image')]
class BookmarkView extends AbstractView implements CursorAwareInterface {
  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $uuid,

    #[ORM\ManyToOne(targetEntity: UserView::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'uuid', nullable: false)]
    private readonly UserView $user,

    #[ORM\ManyToOne(targetEntity: ImageView::class)]
    #[ORM\JoinColumn(name: 'image_id', referencedColumnName: 'uuid', nullable: false)]
    private readonly ImageView $image,

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['public'])]
    private readonly DateTime $createdAt,
  ) {
  }

  #[Groups(['public'])]
  #[SerializedName('id')]
  public function getId(): string {
    return $this->uuid;
  }

  public function getUser(): UserView {
    return $this->user;
  }

  public function getUserId(): string {
    return $this->user->getUuid();
  }

  public function getImage(): ImageView {
    return $this->image;
  }

  public function getImageId(): string {
    return $this->image->getUuid();
  }

  public function getCreatedAt(): DateTime {
    return $this->createdAt;
  }

  public function getCursorId(): string {
    return $this->uuid;
  }

  public function getCursorTimestamp(): DateTimeInterface {
    return $this->createdAt;
  }

  /**
   * @return array{id: string, available: bool, owner?: UserView|GuestUser, attributes?: ImageAttributes, metadata?: ImageMetadata|null}
   */
  #[Groups(['public'])]
  #[SerializedName('image')]
  public function getImageData(): array {
    try {
      $image = $this->image;
      $isAvailable = $image->getAttributes()->isPublic();
    } catch (\Throwable) {
      return [
        'id' => $this->image->getUuid(),
        'available' => false,
      ];
    }

    if (!$isAvailable) {
      return [
        'id' => $image->getUuid(),
        'available' => false,
      ];
    }

    return [
      'id' => $image->getUuid(),
      'available' => true,
      'owner' => $image->getOwner(),
      'attributes' => $image->getAttributes(),
      'metadata' => $image->getMetadata(),
    ];
  }

  #[Groups(['bookmarkers'])]
  #[SerializedName('id')]
  public function getBookmarkerId(): string {
    return $this->user->getUuid();
  }

  #[Groups(['bookmarkers'])]
  #[SerializedName('displayName')]
  public function getBookmarkerDisplayName(): string {
    return $this->user->getDisplayName();
  }

  #[Groups(['bookmarkers'])]
  #[SerializedName('email')]
  public function getBookmarkerEmail(): string {
    return $this->user->getEmail();
  }

  #[Groups(['bookmarkers'])]
  #[SerializedName('bookmarkedAt')]
  public function getBookmarkedAt(): DateTime {
    return $this->createdAt;
  }
}
