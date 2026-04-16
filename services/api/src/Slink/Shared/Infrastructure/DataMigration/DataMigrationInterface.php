<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\DataMigration;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('data_migration')]
interface DataMigrationInterface {
  public function up(): void;

  public function down(): void;

  public function getDescription(): string;
}
