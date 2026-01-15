<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251215110608 extends AbstractMigration {
  public function getDescription(): string {
    return 'Add user_preferences and image_license tables';
  }

  public function up(Schema $schema): void {
    $this->addSql('CREATE TABLE user_preferences (id CHAR(36) NOT NULL, user_id CHAR(36) NOT NULL, preferences CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id))');
    $this->addSql('CREATE UNIQUE INDEX UNIQ_402A6F60A76ED395 ON user_preferences (user_id)');
    $this->addSql('CREATE TABLE image_license (uuid CHAR(36) NOT NULL, image_id CHAR(36) NOT NULL, license VARCHAR(32) NOT NULL, PRIMARY KEY (uuid))');
    $this->addSql('CREATE UNIQUE INDEX UNIQ_IMAGE_LICENSE_IMAGE ON image_license (image_id)');
  }

  public function down(Schema $schema): void {
    $this->addSql('DROP TABLE user_preferences');
    $this->addSql('DROP TABLE image_license');
  }
}
