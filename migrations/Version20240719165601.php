<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240719165601 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP CONSTRAINT fk_d34a04ad1ad5cdbf');
        $this->addSql('DROP INDEX idx_d34a04ad1ad5cdbf');
        $this->addSql('ALTER TABLE product DROP cart_id');
        $this->addSql('ALTER TABLE "user" DROP role');
        $this->addSql('ALTER TABLE "user" ALTER name DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE product ADD cart_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT fk_d34a04ad1ad5cdbf FOREIGN KEY (cart_id) REFERENCES cart (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_d34a04ad1ad5cdbf ON product (cart_id)');
        $this->addSql('ALTER TABLE "user" ADD role INT NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER name SET DEFAULT \'\'');
    }
}
