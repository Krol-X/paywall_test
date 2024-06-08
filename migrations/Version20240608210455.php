<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240608210455 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tariff (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, price INT NOT NULL, discount_percentage INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE payment ADD tariff_id INT NOT NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D92348FD2 FOREIGN KEY (tariff_id) REFERENCES tariff (id)');
        $this->addSql('CREATE INDEX IDX_6D28840D92348FD2 ON payment (tariff_id)');
        $this->addSql('ALTER TABLE payment RENAME INDEX idx_6d28840da76ed395 TO IDX_6D28840DCC0B3066');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D92348FD2');
        $this->addSql('DROP TABLE tariff');
        $this->addSql('DROP INDEX IDX_6D28840D92348FD2 ON payment');
        $this->addSql('ALTER TABLE payment DROP tariff_id');
        $this->addSql('ALTER TABLE payment RENAME INDEX idx_6d28840dcc0b3066 TO IDX_6D28840DA76ED395');
    }
}
