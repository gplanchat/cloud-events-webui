<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241025105809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE greeting_id_seq CASCADE');
        $this->addSql('CREATE TABLE event (id UUID NOT NULL, event_id VARCHAR(255) NOT NULL, spec_version VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, data JSON NOT NULL, source VARCHAR(255) NULL, time TIMESTAMP(0) WITHOUT TIME ZONE NULL, data_content_type VARCHAR(255) NULL, data_schema VARCHAR(255) NULL, subject VARCHAR(255) NULL, extensions JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN event.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN event.time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('DROP TABLE greeting');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE greeting_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE greeting (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('DROP TABLE event');
    }
}
