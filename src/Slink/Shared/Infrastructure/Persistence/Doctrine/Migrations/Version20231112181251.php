<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231112181251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        
        if($platform instanceof \Doctrine\DBAL\Platforms\SqlitePlatform) {
            // this up() migration is auto-generated, please modify it to your needs
            $this->addSql('CREATE TABLE "image" (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
            , file_name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, is_public BOOLEAN NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
            , views INTEGER NOT NULL, size INTEGER NOT NULL, mime_type VARCHAR(255) NOT NULL, width INTEGER NOT NULL, height INTEGER NOT NULL, PRIMARY KEY(uuid))');
            $this->addSql('CREATE TABLE "user" (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
            , email VARCHAR(255) NOT NULL --(DC2Type:email)
            , password VARCHAR(255) NOT NULL --(DC2Type:hashed_password)
            , created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
            , PRIMARY KEY(uuid))');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        
        if($platform instanceof \Doctrine\DBAL\Platforms\SqlitePlatform) {
            // this down() migration is auto-generated, please modify it to your needs
            $this->addSql('DROP TABLE "image"');
            $this->addSql('DROP TABLE "user"');
        }
    }
}
