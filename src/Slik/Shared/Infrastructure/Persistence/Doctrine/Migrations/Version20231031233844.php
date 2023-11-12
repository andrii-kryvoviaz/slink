<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231031233844 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
      
        $platform = $this->connection->getDatabasePlatform();
        
        if($platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform) {
            // this up() migration is auto-generated, please modify it to your needs
            $this->addSql('CREATE TABLE "image" (uuid UUID NOT NULL, file_name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, is_public BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, views INT NOT NULL, PRIMARY KEY(uuid))');
            $this->addSql('COMMENT ON COLUMN "image".uuid IS \'(DC2Type:uuid)\'');
            $this->addSql('COMMENT ON COLUMN "image".created_at IS \'(DC2Type:datetime_immutable)\'');
            $this->addSql('COMMENT ON COLUMN "image".updated_at IS \'(DC2Type:datetime_immutable)\'');
            $this->addSql('ALTER TABLE "user" ALTER email TYPE VARCHAR(255)');
            $this->addSql('ALTER TABLE "user" ALTER password TYPE VARCHAR(255)');
            $this->addSql('COMMENT ON COLUMN "user".email IS \'(DC2Type:email)\'');
            $this->addSql('COMMENT ON COLUMN "user".password IS \'(DC2Type:hashed_password)\'');
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        
        if($platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform) {
            // this down() migration is auto-generated, please modify it to your needs
            $this->addSql('CREATE SCHEMA public');
            $this->addSql('DROP TABLE "image"');
            $this->addSql('ALTER TABLE "user" ALTER email TYPE VARCHAR(255)');
            $this->addSql('ALTER TABLE "user" ALTER password TYPE VARCHAR(255)');
            $this->addSql('COMMENT ON COLUMN "user".email IS NULL');
            $this->addSql('COMMENT ON COLUMN "user".password IS NULL');
        }
    }
}
