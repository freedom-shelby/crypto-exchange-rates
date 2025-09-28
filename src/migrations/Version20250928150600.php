<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250928150600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create exchange_rates table for ExchangeRate entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE exchange_rates (
                    id INT AUTO_INCREMENT NOT NULL,
                    base_currency_id INT NOT NULL,
                    quote_currency_id INT NOT NULL,
                    rate NUMERIC(18, 8) NOT NULL,
                    created_at DATETIME NOT NULL,
                    INDEX idx_currencies_created_at (base_currency_id, quote_currency_id, created_at),
                    INDEX idx_created_at (created_at),
                    INDEX idx_base_currency (base_currency_id),
                    INDEX idx_quote_currency (quote_currency_id),
                    PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4'
        );

        $this->addSql(
            'ALTER TABLE exchange_rates ADD CONSTRAINT FK_BASE_CURRENCY FOREIGN KEY (base_currency_id) REFERENCES currencies (id)'
        );

        $this->addSql(
            'ALTER TABLE exchange_rates ADD CONSTRAINT FK_QUOTE_CURRENCY FOREIGN KEY (quote_currency_id) REFERENCES currencies (id)'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE exchange_rates');
    }
}
