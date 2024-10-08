<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\View;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;
use Slink\User\Infrastructure\ReadModel\Repository\UserRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Table(name: '`user`')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Index(columns: ['uuid'], name: 'idx_user_uuid')]
#[ORM\Index(columns: ['email'], name: 'idx_user_email')]
#[ORM\Index(columns: ['status'], name: 'idx_user_status')]
#[ORM\Index(columns: ['created_at'], name: 'idx_user_created_at')]
class UserView extends AbstractView {
  /**
   * @param string $uuid
   * @param Email $email
   * @param Username $username
   * @param DisplayName $displayName
   * @param HashedPassword $password
   * @param DateTime $createdAt
   * @param DateTime|null $updatedAt
   * @param UserStatus $status
   * @param Collection|null $roles
   */
  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    #[Groups(['public', 'internal'])]
    #[SerializedName('id')]
    private string $uuid,

    #[ORM\Column(type: 'email', unique: true)]
    #[Groups(['public', 'internal'])]
    private Email $email,
    
    #[ORM\Column(type: 'username', unique: true)]
    #[Groups(['public', 'internal'])]
    private Username $username,
    
    #[ORM\Column(type: 'display_name', unique: true, nullable: true)]
    #[Groups(['public', 'internal'])]
    private DisplayName $displayName,

    #[ORM\Column(type: 'hashed_password')]
    private HashedPassword $password,

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['internal'])]
    private readonly DateTime $createdAt,

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['internal'])]
    private ?DateTime $updatedAt,
    
    #[ORM\Column(enumType: UserStatus::class, options: ['default' => UserStatus::Active])]
    #[Groups(['internal', 'status_check'])]
    private UserStatus          $status,
    
    #[ORM\ManyToMany(targetEntity: UserRoleView::class)]
    #[ORM\JoinTable(name: 'user_to_role')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'uuid')]
    #[ORM\InverseJoinColumn(name: 'role', referencedColumnName: 'role')]
    #[Groups(['internal'])]
    private readonly ?Collection $roles,
  ) {
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
  public function getUsername(): string {
    return $this->username->toString();
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
  
  /**
   * @param UserRoleView $role
   * @return void
   */
  public function addRole(UserRoleView $role): void {
    $this->roles?->add($role);
  }
  
  /**
   * @param UserRoleView $role
   * @return void
   */
  public function removeRole(UserRoleView $role): void {
    $this->roles?->removeElement($role);
  }
  
  /**
   * @return array<string>
   */
  public function getRoles(): array {
    return $this->roles?->map(fn(UserRoleView $role) => $role->getRole())->toArray() ?? [];
  }
  
  /**
   * @param HashedPassword $password
   * @return void
   */
  public function setPassword(HashedPassword $password): void {
    $this->password = $password;
  }
  
  /**
   * @param DisplayName $displayName
   * @return void
   */
  public function setDisplayName(DisplayName $displayName): void {
    $this->displayName = $displayName;
  }
}