<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\GetUserCollectionsExists;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class GetUserCollectionsExistsQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\Length(max: 255)]
    private ?string $searchTerm = null,
  ) {
  }

  public function getSearchTerm(): ?string {
    return $this->searchTerm;
  }
}
