<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260119134454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add collection tables and update share for polymorphic relations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE "collection" (uuid CHAR(36) NOT NULL, name VARCHAR(105) NOT NULL, description VARCHAR(500) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, user_id CHAR(36) NOT NULL, PRIMARY KEY (uuid), CONSTRAINT FK_FC4D6532A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_FC4D6532A76ED395 ON "collection" (user_id)');
        $this->addSql('CREATE INDEX idx_collection_user_created_at ON "collection" (user_id, created_at)');

        $this->addSql('CREATE TABLE "collection_item" (uuid CHAR(36) NOT NULL, item_id CHAR(36) NOT NULL, item_type VARCHAR(32) NOT NULL, position DOUBLE PRECISION NOT NULL, added_at DATETIME NOT NULL, collection_id CHAR(36) NOT NULL, PRIMARY KEY (uuid), CONSTRAINT FK_556C09F0514956FD FOREIGN KEY (collection_id) REFERENCES "collection" (uuid) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_556C09F0514956FD ON "collection_item" (collection_id)');
        $this->addSql('CREATE INDEX idx_collection_item_position ON "collection_item" (collection_id, position)');
        $this->addSql('CREATE UNIQUE INDEX unique_collection_item ON "collection_item" (collection_id, item_id)');

        $this->addSql('CREATE TEMPORARY TABLE __temp__share AS SELECT uuid, image_id, target_url, created_at FROM share');
        $this->addSql('DROP TABLE share');
        $this->addSql('CREATE TABLE share (uuid CHAR(36) NOT NULL, target_url VARCHAR(2048) NOT NULL, created_at DATETIME NOT NULL, shareable_id CHAR(36) NOT NULL, shareable_type VARCHAR(32) NOT NULL, PRIMARY KEY (uuid))');
        $this->addSql('INSERT INTO share (uuid, shareable_id, shareable_type, target_url, created_at) SELECT uuid, image_id, \'image\', target_url, created_at FROM __temp__share');
        $this->addSql('DROP TABLE __temp__share');
        $this->addSql('CREATE INDEX idx_share_target_url ON share (target_url)');
        $this->addSql('CREATE INDEX idx_share_shareable ON share (shareable_id, shareable_type)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE "collection_item"');
        $this->addSql('DROP TABLE "collection"');

        $this->addSql('CREATE TEMPORARY TABLE __temp__share AS SELECT uuid, shareable_id, target_url, created_at FROM "share" WHERE shareable_type = \'image\'');
        $this->addSql('DROP TABLE "share"');
        $this->addSql('CREATE TABLE "share" (uuid CHAR(36) NOT NULL, image_id CHAR(36) NOT NULL, target_url VARCHAR(2048) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (uuid), CONSTRAINT FK_EF069D5A3DA5256D FOREIGN KEY (image_id) REFERENCES image (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO "share" (uuid, image_id, target_url, created_at) SELECT uuid, shareable_id, target_url, created_at FROM __temp__share');
        $this->addSql('DROP TABLE __temp__share');
        $this->addSql('CREATE INDEX idx_share_target_url ON "share" (target_url)');
        $this->addSql('CREATE INDEX idx_share_image ON "share" (image_id)');
    }
}
