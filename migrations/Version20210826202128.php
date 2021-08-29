<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210826202128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE files ADD type SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE files ADD status SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE files DROP id_type');
        $this->addSql('ALTER TABLE files DROP id_status');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE files ADD id_type SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE files ADD id_status SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE files DROP type');
        $this->addSql('ALTER TABLE files DROP status');
    }
}
