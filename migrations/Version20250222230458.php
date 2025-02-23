<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250222230458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates table for storing Slack messages identifiers';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE slack_messages (pr_number INTEGER NOT NULL, ts VARCHAR(255) NOT NULL, PRIMARY KEY(pr_number))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE slack_messages');
    }
}
