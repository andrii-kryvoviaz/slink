<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261031161157 extends AbstractMigration {
  public function getDescription(): string {
    return 'Create the messenger failed queue table in the read model database';
  }

  public function up(Schema $schema): void {
    $this->addSql('CREATE TABLE IF NOT EXISTS messenger_messages (
      id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
      body CLOB NOT NULL,
      headers CLOB NOT NULL,
      queue_name VARCHAR(190) NOT NULL,
      created_at DATETIME NOT NULL,
      available_at DATETIME NOT NULL,
      delivered_at DATETIME DEFAULT NULL
    )');
    $this->addSql('CREATE INDEX IF NOT EXISTS IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
    $this->addSql('CREATE INDEX IF NOT EXISTS IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
    $this->addSql('CREATE INDEX IF NOT EXISTS IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
  }

  public function down(Schema $schema): void {
  }
}
