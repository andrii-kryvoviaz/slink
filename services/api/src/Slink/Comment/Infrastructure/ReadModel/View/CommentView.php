<?php

declare(strict_types=1);

namespace Slink\Comment\Infrastructure\ReadModel\View;

use Doctrine\ORM\Mapping as ORM;
use Slink\Comment\Domain\Service\CommentEditPolicy;
use Slink\Comment\Infrastructure\ReadModel\Repository\CommentRepository;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;
use Slink\User\Infrastructure\ReadModel\View\UserView;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Table(name: '`comment`')]
#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\Index(columns: ['image_id', 'created_at'], name: 'idx_comment_image_created')]
#[ORM\Index(columns: ['user_id'], name: 'idx_comment_user')]
#[ORM\Index(columns: ['referenced_comment_id'], name: 'idx_comment_referenced')]
class CommentView extends AbstractView {
  #[ORM\ManyToOne(targetEntity: CommentView::class)]
  #[ORM\JoinColumn(name: 'referenced_comment_id', referencedColumnName: 'uuid', nullable: true)]
  private ?CommentView $referencedComment = null;

  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private readonly string $uuid,

    #[ORM\ManyToOne(targetEntity: ImageView::class)]
    #[ORM\JoinColumn(name: 'image_id', referencedColumnName: 'uuid', nullable: false)]
    private readonly ImageView $image,

    #[ORM\ManyToOne(targetEntity: UserView::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'uuid', nullable: false)]
    #[Groups(['public'])]
    #[SerializedName('author')]
    private readonly UserView $user,

    #[ORM\Column(type: 'text')]
    #[Groups(['public'])]
    private string $content,

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['public'])]
    private readonly DateTime $createdAt,

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['public'])]
    private ?DateTime $updatedAt = null,

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTime $deletedAt = null,
  ) {
  }

  public function setReferencedComment(?CommentView $referencedComment): void {
    $this->referencedComment = $referencedComment;
  }

  #[Groups(['public'])]
  #[SerializedName('id')]
  public function getId(): string {
    return $this->uuid;
  }

  public function getImage(): ImageView {
    return $this->image;
  }

  public function getImageId(): string {
    return $this->image->getUuid();
  }

  public function getUser(): UserView {
    return $this->user;
  }

  public function getUserId(): string {
    return $this->user->getUuid();
  }

  public function getReferencedComment(): ?CommentView {
    return $this->referencedComment;
  }

  #[Groups(['public'])]
  #[SerializedName('referencedComment')]
  public function getReferencedCommentSummary(): ?array {
    if ($this->referencedComment === null) {
      return null;
    }

    return [
      'id' => $this->referencedComment->getId(),
      'author' => [
        'id' => $this->referencedComment->getUser()->getUuid(),
        'displayName' => $this->referencedComment->getUser()->getDisplayName(),
      ],
      'isDeleted' => $this->referencedComment->isDeleted(),
      'displayContent' => $this->referencedComment->getDisplayContent(),
    ];
  }

  public function getContent(): string {
    return $this->content;
  }

  #[Groups(['public'])]
  #[SerializedName('displayContent')]
  public function getDisplayContent(): string {
    if ($this->isDeleted()) {
      return '[deleted]';
    }

    return $this->content;
  }

  public function getCreatedAt(): DateTime {
    return $this->createdAt;
  }

  public function getUpdatedAt(): ?DateTime {
    return $this->updatedAt;
  }

  public function getDeletedAt(): ?DateTime {
    return $this->deletedAt;
  }

  #[Groups(['public'])]
  #[SerializedName('isDeleted')]
  public function isDeleted(): bool {
    return $this->deletedAt !== null;
  }

  #[Groups(['public'])]
  #[SerializedName('isEdited')]
  public function isEdited(): bool {
    return $this->updatedAt !== null && !$this->isDeleted();
  }

  #[Groups(['public'])]
  #[SerializedName('canEdit')]
  public function canEdit(): bool {
    if ($this->isDeleted()) {
      return false;
    }
    return CommentEditPolicy::canEdit($this->createdAt);
  }

  public function updateContent(string $content, DateTime $updatedAt): void {
    $this->content = $content;
    $this->updatedAt = $updatedAt;
  }

  public function markAsDeleted(DateTime $deletedAt): void {
    $this->deletedAt = $deletedAt;
  }
}
