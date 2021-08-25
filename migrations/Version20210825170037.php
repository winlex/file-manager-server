<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210825170037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE status_id_status_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE type_id_type_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE status (id_status INT NOT NULL, name_status VARCHAR(255) NOT NULL, PRIMARY KEY(id_status))');
        $this->addSql('CREATE TABLE type (id_type INT NOT NULL, name_type VARCHAR(255) NOT NULL, PRIMARY KEY(id_type))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE status_id_status_seq CASCADE');
        $this->addSql('DROP SEQUENCE type_id_type_seq CASCADE');
        $this->addSql('DROP TABLE status');
        $this->addSql('DROP TABLE type');
    }
}
