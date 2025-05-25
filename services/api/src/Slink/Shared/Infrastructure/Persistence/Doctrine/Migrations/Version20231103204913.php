<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231103204913 extends AbstractMigration
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
            $this->addSql('ALTER TABLE image DROP time_created');
            $this->addSql('ALTER TABLE image DROP time_modified');
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        
        if($platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform) {
            // this down() migration is auto-generated, please modify it to your needs
            $this->addSql('CREATE SCHEMA public');
            $this->addSql('ALTER TABLE "image" ADD time_created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
            $this->addSql('ALTER TABLE "image" ADD time_modified TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
            $this->addSql('COMMENT ON COLUMN "image".time_created IS \'(DC2Type:datetime_immutable)\'');
            $this->addSql('COMMENT ON COLUMN "image".time_modified IS \'(DC2Type:datetime_immutable)\'');
        }
    }
}
