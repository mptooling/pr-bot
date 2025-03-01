<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250301151554 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates table for mapping GitHub repositories to Slack channels';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'EOF'
CREATE TABLE github_slack_mapping (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    repository VARCHAR(255) NOT NULL,
    slack_channel VARCHAR(255) NOT NULL, 
    mentions CLOB NOT NULL)
EOF
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1A21BE855CFE57CD ON github_slack_mapping (repository)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE github_slack_mapping');
    }
}
