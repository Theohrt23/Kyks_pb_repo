<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230424100259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE carts (id INT AUTO_INCREMENT NOT NULL, user_id_id INT NOT NULL, UNIQUE INDEX UNIQ_4E004AAC9D86650F (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE carts_products (carts_id INT NOT NULL, products_id INT NOT NULL, INDEX IDX_12E5DBFBBCB5C6F5 (carts_id), INDEX IDX_12E5DBFB6C8A81A9 (products_id), PRIMARY KEY(carts_id, products_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE carts ADD CONSTRAINT FK_4E004AAC9D86650F FOREIGN KEY (user_id_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE carts_products ADD CONSTRAINT FK_12E5DBFBBCB5C6F5 FOREIGN KEY (carts_id) REFERENCES carts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carts_products ADD CONSTRAINT FK_12E5DBFB6C8A81A9 FOREIGN KEY (products_id) REFERENCES products (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE carts DROP FOREIGN KEY FK_4E004AAC9D86650F');
        $this->addSql('ALTER TABLE carts_products DROP FOREIGN KEY FK_12E5DBFBBCB5C6F5');
        $this->addSql('ALTER TABLE carts_products DROP FOREIGN KEY FK_12E5DBFB6C8A81A9');
        $this->addSql('DROP TABLE carts');
        $this->addSql('DROP TABLE carts_products');
    }
}
