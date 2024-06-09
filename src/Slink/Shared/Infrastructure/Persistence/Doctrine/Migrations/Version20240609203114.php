<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240609203114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__image AS SELECT uuid, user_id, file_name, description, is_public, created_at, updated_at, views, size, mime_type, width, height FROM image');
        $this->addSql('DROP TABLE image');
        $this->addSql('CREATE TABLE image (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
        , user_id CHAR(36) DEFAULT NULL --(DC2Type:uuid)
        , file_name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, is_public BOOLEAN NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , views INTEGER NOT NULL, size INTEGER NOT NULL, mime_type VARCHAR(255) NOT NULL, width INTEGER NOT NULL, height INTEGER NOT NULL, PRIMARY KEY(uuid), CONSTRAINT FK_C53D045FA76ED395 FOREIGN KEY (user_id) REFERENCES user (uuid) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO image (uuid, user_id, file_name, description, is_public, created_at, updated_at, views, size, mime_type, width, height) SELECT uuid, user_id, file_name, description, is_public, created_at, updated_at, views, size, mime_type, width, height FROM __temp__image');
        $this->addSql('DROP TABLE __temp__image');
        $this->addSql('CREATE INDEX idx_image_uuid ON image (uuid)');
        $this->addSql('CREATE INDEX idx_image_created_at ON image (created_at)');
        $this->addSql('CREATE INDEX idx_image_user_created_at ON image (user_id, created_at)');
        $this->addSql('CREATE INDEX idx_image_user_id ON image (user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT uuid, email, password, created_at, updated_at, display_name, status FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
        , email VARCHAR(255) NOT NULL --(DC2Type:email)
        , password VARCHAR(255) NOT NULL --(DC2Type:hashed_password)
        , created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , display_name VARCHAR(255) DEFAULT NULL --(DC2Type:display_name)
        , status VARCHAR(255) DEFAULT \'active\' NOT NULL, PRIMARY KEY(uuid))');
        $this->addSql('INSERT INTO user (uuid, email, password, created_at, updated_at, display_name, status) SELECT uuid, email, password, created_at, updated_at, display_name, status FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649D5499347 ON user (display_name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('CREATE INDEX idx_user_uuid ON user (uuid)');
        $this->addSql('CREATE INDEX idx_user_email ON user (email)');
        $this->addSql('CREATE INDEX idx_user_status ON user (status)');
        $this->addSql('CREATE INDEX idx_user_created_at ON user (created_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__image AS SELECT uuid, user_id, file_name, description, is_public, created_at, updated_at, views, size, mime_type, width, height FROM "image"');
        $this->addSql('DROP TABLE "image"');
        $this->addSql('CREATE TABLE "image" (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
        , user_id CHAR(36) DEFAULT NULL --(DC2Type:uuid)
        , file_name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, is_public BOOLEAN NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , views INTEGER NOT NULL, size INTEGER NOT NULL, mime_type VARCHAR(255) NOT NULL, width INTEGER NOT NULL, height INTEGER NOT NULL, PRIMARY KEY(uuid), CONSTRAINT FK_C53D045FA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO "image" (uuid, user_id, file_name, description, is_public, created_at, updated_at, views, size, mime_type, width, height) SELECT uuid, user_id, file_name, description, is_public, created_at, updated_at, views, size, mime_type, width, height FROM __temp__image');
        $this->addSql('DROP TABLE __temp__image');
        $this->addSql('CREATE INDEX IDX_C53D045FA76ED395 ON "image" (user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT uuid, email, display_name, password, created_at, updated_at, status FROM "user"');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('CREATE TABLE "user" (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
        , email VARCHAR(255) NOT NULL --(DC2Type:email)
        , display_name VARCHAR(255) DEFAULT NULL --(DC2Type:display_name)
        , password VARCHAR(255) NOT NULL --(DC2Type:hashed_password)
        , created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , status VARCHAR(255) DEFAULT \'active\' NOT NULL, PRIMARY KEY(uuid))');
        $this->addSql('INSERT INTO "user" (uuid, email, display_name, password, created_at, updated_at, status) SELECT uuid, email, display_name, password, created_at, updated_at, status FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649D5499347 ON "user" (display_name)');
    }
}
