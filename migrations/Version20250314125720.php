<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250314125720 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds `gh_repository` column to slack_messages table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE slack_messages ADD gh_repository VARCHAR(255) NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE slack_messages DROP COLUMN gh_repository');
    }
}
