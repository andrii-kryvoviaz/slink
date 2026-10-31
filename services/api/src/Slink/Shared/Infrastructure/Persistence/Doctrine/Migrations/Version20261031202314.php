<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261031202314 extends AbstractMigration {
  public function getDescription(): string {
    return 'Drop the stored bookmark_count column from the image table, the count is derived from the bookmark rows';
  }

  public function up(Schema $schema): void {
    $this->addSql('ALTER TABLE "image" DROP COLUMN bookmark_count');
  }

  public function down(Schema $schema): void {
    $this->addSql('ALTER TABLE "image" ADD COLUMN bookmark_count INTEGER DEFAULT 0 NOT NULL');
    $this->addSql(
      'UPDATE "image" SET bookmark_count = (SELECT COUNT(*) FROM "bookmark" WHERE "bookmark".image_id = "image".uuid)'
    );
  }
}
