<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220329141227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE atm (id VARCHAR(6) NOT NULL, cluster_id VARCHAR(32) NOT NULL, address VARCHAR(255) NOT NULL, usd INT NOT NULL, rub INT NOT NULL, eur INT NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_CE04D912C36A3328 (cluster_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cluster (id VARCHAR(32) NOT NULL, bottom_left_latitude DOUBLE PRECISION NOT NULL, bottom_left_longitude DOUBLE PRECISION NOT NULL, top_right_latitude DOUBLE PRECISION NOT NULL, top_right_longitude DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conversation (id INT AUTO_INCREMENT NOT NULL, chat_id VARCHAR(255) NOT NULL, command_name VARCHAR(255) NOT NULL, state INT NOT NULL, data JSON NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, atm_id VARCHAR(6) NOT NULL, chat_id VARCHAR(255) NOT NULL, currency VARCHAR(3) NOT NULL, amount INT NOT NULL, INDEX IDX_BF5476CA2E7408AF (atm_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE atm ADD CONSTRAINT FK_CE04D912C36A3328 FOREIGN KEY (cluster_id) REFERENCES cluster (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA2E7408AF FOREIGN KEY (atm_id) REFERENCES atm (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA2E7408AF');
        $this->addSql('ALTER TABLE atm DROP FOREIGN KEY FK_CE04D912C36A3328');
        $this->addSql('DROP TABLE atm');
        $this->addSql('DROP TABLE cluster');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE notification');
    }
}
