<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\DependencyInjection\ServiceLocator;

interface Indexable {
  public static function getIndexName(): string;
}