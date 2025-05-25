<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240218225943 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT uuid, email, password, created_at, updated_at FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
        , email VARCHAR(255) NOT NULL --(DC2Type:email)
        , password VARCHAR(255) NOT NULL --(DC2Type:hashed_password)
        , created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , display_name VARCHAR(255) DEFAULT NULL --(DC2Type:display_name)
        , PRIMARY KEY(uuid))');
        $this->addSql('INSERT INTO user (uuid, email, password, created_at, updated_at) SELECT uuid, email, password, created_at, updated_at FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649D5499347 ON user (display_name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT uuid, email, password, created_at, updated_at FROM "user"');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('CREATE TABLE "user" (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
        , email VARCHAR(255) NOT NULL --(DC2Type:email)
        , password VARCHAR(255) NOT NULL --(DC2Type:hashed_password)
        , created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , PRIMARY KEY(uuid))');
        $this->addSql('INSERT INTO "user" (uuid, email, password, created_at, updated_at) SELECT uuid, email, password, created_at, updated_at FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
    }
}
