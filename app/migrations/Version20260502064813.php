<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260502064813 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE supplier_product (id INT AUTO_INCREMENT NOT NULL, purchase_price NUMERIC(10, 2) NOT NULL, product_id INT NOT NULL, supplier_id INT NOT NULL, INDEX IDX_522F70B24584665A (product_id), INDEX IDX_522F70B22ADD6D8C (supplier_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE supplier_product ADD CONSTRAINT FK_522F70B24584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE supplier_product ADD CONSTRAINT FK_522F70B22ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier_profile (id)');
        $this->addSql('ALTER TABLE product DROP purchase_price');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supplier_product DROP FOREIGN KEY FK_522F70B24584665A');
        $this->addSql('ALTER TABLE supplier_product DROP FOREIGN KEY FK_522F70B22ADD6D8C');
        $this->addSql('DROP TABLE supplier_product');
        $this->addSql('ALTER TABLE product ADD purchase_price DOUBLE PRECISION NOT NULL');
    }
}
