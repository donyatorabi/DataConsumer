<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250703132425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audit_log DROP total_rows');
        $this->addSql('ALTER TABLE import_status CHANGE total_rows total_rows INT NOT NULL, CHANGE processed_rows processed_rows INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audit_log ADD total_rows INT DEFAULT NULL');
        $this->addSql('ALTER TABLE import_status CHANGE total_rows total_rows INT DEFAULT NULL, CHANGE processed_rows processed_rows INT DEFAULT NULL');
    }
}
