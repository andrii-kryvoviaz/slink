<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260725154530 extends AbstractMigration {
  public function getDescription(): string {
    return 'Make user.email and user.username nullable and drop the user.display_name unique index so deleted users release their identity';
  }

  public function up(Schema $schema): void {
    $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT uuid, email, username, password, created_at, updated_at, display_name, status FROM "user"');
    $this->addSql('DROP TABLE "user"');
    $this->addSql('CREATE TABLE "user" (uuid CHAR(36) NOT NULL, email VARCHAR(255) DEFAULT NULL, username VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, display_name VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT \'active\' NOT NULL, PRIMARY KEY(uuid))');
    $this->addSql('INSERT INTO "user" (uuid, email, username, password, created_at, updated_at, display_name, status) SELECT uuid, email, username, password, created_at, updated_at, display_name, status FROM __temp__user');
    $this->addSql('DROP TABLE __temp__user');
    $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
    $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON "user" (username)');
    $this->addSql('CREATE INDEX idx_user_email ON "user" (email)');
    $this->addSql('CREATE INDEX idx_user_username ON "user" (username)');
    $this->addSql('CREATE INDEX idx_user_display_name ON "user" (display_name)');
    $this->addSql('CREATE INDEX idx_user_status ON "user" (status)');
    $this->addSql('CREATE INDEX idx_user_created_at ON "user" (created_at)');
  }

  public function down(Schema $schema): void {
  }
}
