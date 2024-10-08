<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\UpdateProfile;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateProfileCommand implements CommandInterface {
  use EnvelopedMessage;
  
  public function __construct(
    #[Assert\Length(min: 3, max: 30)]
    #[Assert\Regex(pattern: '/^[a-zA-Z0-9_\-\s\.]+$/', message: 'Display name can only contain letters, numbers, underscores, hyphens, spaces, and periods.')]
    #[Assert\Regex(pattern: '/^(?!.*(_|-|\s\.){2})/', message: 'Display name cannot contain consecutive characters of the same type.')]
    #[Assert\Regex(pattern: '/^(?!anonymous$)/i', message: '`Anonymous` is a reserved display name.')]
    private ?string $display_name = null
  ) {
  }
  
  /*
   * @return string
   */
  public function getDisplayName(): ?string {
    return $this->display_name;
  }
}