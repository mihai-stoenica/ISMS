<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260407123028 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE supplier_profile (id INT AUTO_INCREMENT NOT NULL, unique_identifier VARCHAR(10) NOT NULL, phone_number VARCHAR(10) NOT NULL, address VARCHAR(255) NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_32DE10A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE supplier_profile ADD CONSTRAINT FK_32DE10A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supplier_profile DROP FOREIGN KEY FK_32DE10A76ED395');
        $this->addSql('DROP TABLE supplier_profile');
    }
}
