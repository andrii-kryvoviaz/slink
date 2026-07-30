<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\PurgeUser;

use Slink\Shared\Application\Command\CommandInterface;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class PurgeUserCommand implements CommandInterface {
  /**
   * @param string $id
   */
  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private string $id,
  ) {
  }

  /**
   * @return string
   */
  public function getId(): string {
    return $this->id;
  }
}
