<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Query\GetTagById;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class GetTagByIdQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private string $id,
  ) {}

  public function getId(): string {
    return $this->id;
  }
}