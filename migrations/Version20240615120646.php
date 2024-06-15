<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240615120646 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "order" ADD delivery_address_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "order" ALTER delivery_type DROP DEFAULT');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F5299398EBF23851 FOREIGN KEY (delivery_address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F5299398EBF23851 ON "order" (delivery_address_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F5299398EBF23851');
        $this->addSql('DROP INDEX IDX_F5299398EBF23851');
        $this->addSql('ALTER TABLE "order" DROP delivery_address_id');
        $this->addSql('ALTER TABLE "order" ALTER delivery_type SET DEFAULT \'selfdelivery\'');
    }
}
