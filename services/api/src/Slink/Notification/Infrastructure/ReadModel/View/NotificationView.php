<?php

declare(strict_types=1);

namespace Slink\Notification\Infrastructure\ReadModel\View;

use Doctrine\ORM\Mapping as ORM;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Notification\Domain\Enum\NotificationType;
use Slink\Notification\Infrastructure\ReadModel\Repository\NotificationRepository;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;
use Slink\User\Infrastructure\ReadModel\View\UserView;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Table(name: '`notification`')]
#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Index(columns: ['user_id', 'is_read', 'created_at'], name: 'idx_notification_user_read_created')]
#[ORM\Index(columns: ['user_id', 'created_at'], name: 'idx_notification_user_created')]
class NotificationView extends AbstractView {
  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $uuid,

    #[ORM\ManyToOne(targetEntity: UserView::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'uuid', nullable: false)]
    private readonly UserView $user,

    #[ORM\Column(type: 'string', length: 50, enumType: NotificationType::class)]
    private readonly NotificationType $type,

    #[ORM\ManyToOne(targetEntity: ImageView::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'reference_id', referencedColumnName: 'uuid', nullable: false)]
    private readonly ImageView $reference,

    #[ORM\ManyToOne(targetEntity: CommentView::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'related_comment_id', referencedColumnName: 'uuid', nullable: true)]
    private readonly ?CommentView $relatedComment,

    #[ORM\ManyToOne(targetEntity: UserView::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'actor_id', referencedColumnName: 'uuid', nullable: true)]
    private readonly ?UserView $actor,

    #[ORM\Column(type: 'datetime_immutable')]
    private readonly DateTime $createdAt,

    #[ORM\Column(type: 'boolean')]
    private bool $isRead = false,

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTime $readAt = null,
  ) {
  }

  #[Groups(['public'])]
  #[SerializedName('id')]
  public function getId(): string {
    return $this->uuid;
  }

  #[Groups(['public'])]
  #[SerializedName('type')]
  public function getTypeValue(): string {
    return $this->type->value;
  }

  #[Groups(['public'])]
  #[SerializedName('isRead')]
  public function getIsRead(): bool {
    return $this->isRead;
  }

  /**
   * @return array{formattedDate: string, timestamp: int}
   */
  #[Groups(['public'])]
  #[SerializedName('createdAt')]
  public function getCreatedAtFormatted(): array {
    return [
      'formattedDate' => $this->createdAt->format('Y-m-d H:i:s'),
      'timestamp' => $this->createdAt->getTimestamp(),
    ];
  }

  /**
   * @return array<string, string>|null
   */
  #[Groups(['public'])]
  #[SerializedName('actor')]
  public function getActorSummary(): ?array {
    if ($this->actor === null) {
      return null;
    }

    return [
      'id' => $this->actor->getUuid(),
      'email' => $this->actor->getEmail(),
      'username' => $this->actor->getUsername(),
      'displayName' => $this->actor->getDisplayName(),
    ];
  }

  public function getUser(): UserView {
    return $this->user;
  }

  public function getUserId(): string {
    return $this->user->getUuid();
  }

  public function getType(): NotificationType {
    return $this->type;
  }

  #[Groups(['public'])]
  #[SerializedName('message')]
  public function getMessage(): string {
    return $this->type->getLabel();
  }

  public function getReference(): ImageView {
    return $this->reference;
  }

  /**
   * @return array<string, string>
   */
  #[Groups(['public'])]
  #[SerializedName('reference')]
  public function getReferenceSummary(): array {
    return [
      'id' => $this->reference->getUuid(),
      'fileName' => $this->reference->getAttributes()->getFileName(),
    ];
  }

  public function getRelatedComment(): ?CommentView {
    return $this->relatedComment;
  }

  /**
   * @return array<string, mixed>|null
   */
  #[Groups(['public'])]
  #[SerializedName('relatedComment')]
  public function getRelatedCommentSummary(): ?array {
    if ($this->relatedComment === null) {
      return null;
    }

    return [
      'id' => $this->relatedComment->getId(),
      'content' => $this->relatedComment->getDisplayContent(),
      'isDeleted' => $this->relatedComment->isDeleted(),
    ];
  }

  public function getActor(): ?UserView {
    return $this->actor;
  }

  public function getCreatedAt(): DateTime {
    return $this->createdAt;
  }

  public function isRead(): bool {
    return $this->isRead;
  }

  public function getReadAt(): ?DateTime {
    return $this->readAt;
  }

  public function markAsRead(DateTime $readAt): void {
    $this->isRead = true;
    $this->readAt = $readAt;
  }
}
