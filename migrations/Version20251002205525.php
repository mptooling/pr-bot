<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251002205525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update `slack_messages` table schema so the primary key is a composite key of `pr_number` and `gh_repository`';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE slack_messages_new (pr_number INTEGER NOT NULL, ts VARCHAR(255) NOT NULL, gh_repository VARCHAR(255) NOT NULL, PRIMARY KEY(pr_number, gh_repository))');
        $this->addSql('INSERT INTO slack_messages_new (pr_number, ts, gh_repository) SELECT pr_number, ts, gh_repository FROM slack_messages');
        $this->addSql('DROP TABLE slack_messages');
        $this->addSql('ALTER TABLE slack_messages_new RENAME TO slack_messages');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE slack_messages_new (pr_number INTEGER NOT NULL, ts VARCHAR(255) NOT NULL, gh_repository VARCHAR(255) NOT NULL, PRIMARY KEY(pr_number))');
        $this->addSql('INSERT INTO slack_messages_new (pr_number, ts, gh_repository) SELECT pr_number, ts, gh_repository FROM slack_messages');
        $this->addSql('DROP TABLE slack_messages');
        $this->addSql('ALTER TABLE slack_messages_new RENAME TO slack_messages');
    }
}
