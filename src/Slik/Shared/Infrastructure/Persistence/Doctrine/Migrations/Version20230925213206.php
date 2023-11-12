<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230925213206 extends AbstractMigration
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
          $this->addSql('CREATE TABLE "user" (uuid UUID NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(uuid))');
          $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
          $this->addSql('COMMENT ON COLUMN "user".uuid IS \'(DC2Type:uuid)\'');
          $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetime_immutable)\'');
          $this->addSql('COMMENT ON COLUMN "user".updated_at IS \'(DC2Type:datetime_immutable)\'');
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        
        if($platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform) {
          // this down() migration is auto-generated, please modify it to your needs
          $this->addSql('CREATE SCHEMA public');
          $this->addSql('DROP TABLE "user"');
        }
    }
}
