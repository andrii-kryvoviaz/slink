<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Query;

use Symfony\Component\Messenger\Envelope;

interface QueryBusInterface {
  public function ask(QueryInterface|Envelope $query): mixed;
}