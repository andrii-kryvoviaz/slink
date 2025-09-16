<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\EventStore\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250916000000 extends AbstractMigration {
  
  public function getDescription(): string {
    return 'Create snapshots table for aggregate snapshotting';
  }
  
  public function up(Schema $schema): void {
    $this->addSql('CREATE TABLE snapshots (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      aggregate_root_id VARCHAR(36) NOT NULL UNIQUE,
      aggregate_version INTEGER NOT NULL,
      state TEXT NOT NULL,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
    
    $this->addSql('CREATE INDEX idx_created_at ON snapshots (created_at)');
  }
  
  public function down(Schema $schema): void {
    $this->addSql('DROP TABLE snapshots');
  }
}