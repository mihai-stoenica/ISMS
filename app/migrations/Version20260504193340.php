<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260504193340 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contract (id INT AUTO_INCREMENT NOT NULL, date DATE DEFAULT NULL, total_cost NUMERIC(10, 2) NOT NULL, status VARCHAR(255) NOT NULL, quantity INT NOT NULL, manager_id INT NOT NULL, supplier_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_E98F2859783E3463 (manager_id), INDEX IDX_E98F28592ADD6D8C (supplier_id), INDEX IDX_E98F28594584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F2859783E3463 FOREIGN KEY (manager_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F28592ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier_profile (id)');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F28594584665A FOREIGN KEY (product_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F2859783E3463');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F28592ADD6D8C');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F28594584665A');
        $this->addSql('DROP TABLE contract');
    }
}
