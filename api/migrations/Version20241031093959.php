<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241031093959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX IDX_3BAE0AA771F7E88B ON event (event_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA76F949845 ON event (time)');
        $this->addSql('CREATE INDEX IDX_AD005B69A64CC256 ON subscriber (service_uri)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX IDX_3BAE0AA771F7E88B');
        $this->addSql('DROP INDEX IDX_3BAE0AA76F949845');
        $this->addSql('DROP INDEX IDX_AD005B69A64CC256');
    }
}
