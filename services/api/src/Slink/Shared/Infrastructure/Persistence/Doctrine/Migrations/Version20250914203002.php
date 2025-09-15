<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250914203002 extends AbstractMigration {
  public function getDescription(): string {
    return 'Add sha1_hash field to image table for deduplication';
  }

  public function up(Schema $schema): void {
    $this->addSql('ALTER TABLE "image" ADD COLUMN sha1_hash VARCHAR(40) DEFAULT NULL');
    $this->addSql('CREATE INDEX idx_image_sha1_hash ON "image" (sha1_hash)');
  }

  public function down(Schema $schema): void {
    $this->addSql('DROP INDEX idx_image_sha1_hash');
    $this->addSql('ALTER TABLE "image" DROP COLUMN sha1_hash');
  }
}