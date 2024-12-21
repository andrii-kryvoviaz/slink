<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\View;

use Doctrine\ORM\Mapping as ORM;
use Slink\User\Infrastructure\ReadModel\Repository\UserRoleRepository;

#[ORM\Table(name: '`user_role`')]
#[ORM\Entity(repositoryClass: UserRoleRepository::class)]
class UserRoleView {
  
  /**
   * @param string $role
   * @param string $name
   */
  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private readonly string $role,
    
    #[ORM\Column(type: 'string')]
    private readonly string $name,
  ) {
  }
  
  /**
   * @return string
   */
  public function getRole(): string {
    return $this->role;
  }
  
  /**
   * @return string
   */
  public function getName(): string {
    return $this->name;
  }
}