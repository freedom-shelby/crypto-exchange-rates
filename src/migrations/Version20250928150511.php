<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250928150511 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create currencies table for Currency entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE currencies (
                    id INT AUTO_INCREMENT NOT NULL,
                    code VARCHAR(10) NOT NULL,
                    name VARCHAR(100) NOT NULL,
                    symbol VARCHAR(10) DEFAULT NULL,
                    is_active TINYINT(1) NOT NULL,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME DEFAULT NULL,
                    UNIQUE INDEX unique_currency_code (code),
                    PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE currencies');
    }
}
