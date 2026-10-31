<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261031113024 extends AbstractMigration {
  private const array ROOT_TABLES = [
    'user',
    'image',
    'share',
    'collection',
    'comment',
    'tag',
    'bookmark',
    'notification',
    'oauth_provider',
  ];

  public function getDescription(): string {
    return 'Add the optimistic locking version column to the root view tables';
  }

  public function up(Schema $schema): void {
    foreach (self::ROOT_TABLES as $table) {
      $this->addSql(\sprintf('ALTER TABLE "%s" ADD COLUMN version INTEGER DEFAULT 1 NOT NULL', $table));
    }
  }

  public function down(Schema $schema): void {
  }
}
