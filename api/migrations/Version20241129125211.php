<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241129125211 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscriber ADD description VARCHAR(65535) DEFAULT NULL');
        $this->addSql('ALTER TABLE subscriber ADD code VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE subscriber ALTER label SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE subscriber DROP description');
        $this->addSql('ALTER TABLE subscriber DROP code');
        $this->addSql('ALTER TABLE subscriber ALTER label DROP NOT NULL');
    }
}
