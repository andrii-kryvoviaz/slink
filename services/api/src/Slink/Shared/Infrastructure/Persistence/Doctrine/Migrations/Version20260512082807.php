<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260512082807 extends AbstractMigration {
  public function getDescription(): string {
    return 'Widen short_url.short_code to accommodate configurable short URL length (up to 32 chars)';
  }

  public function up(Schema $schema): void {
    $schema->getTable('short_url')->getColumn('short_code')->setLength(32);
  }

  public function down(Schema $schema): void {
    $schema->getTable('short_url')->getColumn('short_code')->setLength(8);
  }
}
