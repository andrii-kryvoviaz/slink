<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Query;

use Symfony\Component\Messenger\Envelope;
use Symfony\Contracts\Service\Attribute\Required;

trait QueryTrait {
  private readonly QueryBusInterface $queryBus;

  #[Required]
  public function setQueryBus(QueryBusInterface $queryBus): void {
    $this->queryBus = $queryBus;
  }

  protected function ask(QueryInterface|Envelope $query): mixed {
    return $this->queryBus->ask($query);
  }
}