<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageListById;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class GetImageListByIdQuery implements QueryInterface {
  use EnvelopedMessage;
  
  /**
   * @param array<string> $uuid
   */
  public function __construct(
    #[Assert\NotBlank]
    #[Assert\All([
      new Assert\Uuid
    ])]
    private array $uuid = []
  ) {
  }
  
  /**
   * @return array<string>
   */
  public function getUuid(): array {
    return $this->uuid;
  }
}