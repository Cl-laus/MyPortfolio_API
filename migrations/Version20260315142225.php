<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260315142225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE information (id INT AUTO_INCREMENT NOT NULL, full_name VARCHAR(255) NOT NULL, job_title VARCHAR(255) NOT NULL, tag_line VARCHAR(255) NOT NULL, intro_text LONGTEXT NOT NULL, photo_path VARCHAR(255) DEFAULT NULL, about_title VARCHAR(255) DEFAULT NULL, about_text LONGTEXT NOT NULL, email VARCHAR(255) NOT NULL, cv VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE information');
    }
}
