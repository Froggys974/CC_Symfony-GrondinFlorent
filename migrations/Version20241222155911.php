<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20241222155911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(sql: 'ALTER TABLE post ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');

    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post DROP created_at');
    }
}
